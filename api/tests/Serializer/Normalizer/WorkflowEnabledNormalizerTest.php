<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Normalizer;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use App\Serializer\Normalizer\WorkflowEnabledNormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\TransitionBlocker;
use Symfony\Component\Workflow\TransitionBlockerList;
use Symfony\Component\Workflow\WorkflowInterface;

final class WorkflowEnabledNormalizerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $workflowRegistry;
    private ObjectProphecy $decorated;
    private WorkflowEnabledNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->workflowRegistry = $this->prophesize(Registry::class);
        $this->decorated = $this->prophesize(NormalizerInterface::class);
        $this->normalizer = new WorkflowEnabledNormalizer($this->workflowRegistry->reveal());
        $this->normalizer->setNormalizer($this->decorated->reveal());
    }

    public function testSupportsNormalization(): void
    {
        self::assertTrue($this->normalizer->supportsNormalization(new \stdClass(), null, [AbstractNormalizer::GROUPS => ['workflow', 'other']]));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass(), null, [AbstractNormalizer::GROUPS => ['other']]));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass(), null, [AbstractNormalizer::GROUPS => ['workflow'], 'WORKFLOW_ALREADY_CHECKED' => true]));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testGetSupportedTypes(): void
    {
        $result = $this->normalizer->getSupportedTypes(null);

        $this->assertSame(['*' => false], $result);
    }

    public function testNormalizeReturnsNonArrayDataAsIs(): void
    {
        $object = new \stdClass();

        $this->decorated
            ->normalize($object, null, ['WORKFLOW_ALREADY_CHECKED' => $object])
            ->shouldBeCalledOnce()
            ->willReturn('string_value')
        ;

        $result = $this->normalizer->normalize($object);

        $this->assertSame('string_value', $result);
    }

    public function testNormalizeThrowsExceptionWhenWorkflowNotFound(): void
    {
        $object = new \stdClass();

        $this->decorated
            ->normalize($object, null, Argument::type('array'))
            ->shouldBeCalledOnce()
            ->willReturn(['id' => 1])
        ;

        $this->workflowRegistry
            ->get($object)
            ->shouldBeCalledOnce()
            ->willThrow(new InvalidArgumentException())
        ;

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Unable to find a workflow for class "stdClass".');

        $this->normalizer->normalize($object);
    }

    public function testNormalizeAddsAvailableStatusAndBlockers(): void
    {
        $object = new \stdClass();
        $normalizedData = ['id' => 1, 'name' => 'test'];

        $this->decorated
            ->normalize($object, null, Argument::type('array'))
            ->shouldBeCalledOnce()
            ->willReturn($normalizedData)
        ;

        $marking = new Marking(['draft' => 1]);

        $transition1 = new Transition('to_published', 'draft', 'published');
        $transition2 = new Transition('to_archived', 'draft', 'archived');

        $definition = new Definition(['draft', 'published', 'archived'], [$transition1, $transition2]);

        $workflow = $this->prophesize(WorkflowInterface::class);
        $workflow
            ->getMarking($object)
            ->shouldBeCalledOnce()
            ->willReturn($marking)
        ;

        $workflow
            ->getDefinition()
            ->shouldBeCalledOnce()
            ->willReturn($definition)
        ;

        $workflow
            ->can($object, 'to_published')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $workflow
            ->can($object, 'to_archived')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $blockerList = new TransitionBlockerList([
            new TransitionBlocker('Missing permission', TransitionBlocker::UNKNOWN),
        ]);

        $workflow
            ->buildTransitionBlockerList($object, 'to_archived')
            ->shouldBeCalledOnce()
            ->willReturn($blockerList)
        ;

        $this->workflowRegistry
            ->get($object)
            ->shouldBeCalledOnce()
            ->willReturn($workflow->reveal())
        ;

        $result = $this->normalizer->normalize($object);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('availableStatus', $result);
        $this->assertArrayHasKey('statusBlockerMessage', $result);
        $this->assertSame(['published'], $result['availableStatus']);
        $this->assertCount(1, $result['statusBlockerMessage']);
        $this->assertSame([
            [
                'from' => 'draft',
                'to' => 'archived',
                'message' => 'Missing permission',
            ],
        ], $result['statusBlockerMessage']);
    }

    public function testNormalizeSkipsTransitionsNotFromCurrentPlace(): void
    {
        $object = new \stdClass();
        $normalizedData = ['id' => 1];

        $this->decorated
            ->normalize($object, null, Argument::type('array'))
            ->shouldBeCalledOnce()
            ->willReturn($normalizedData)
        ;

        $marking = new Marking(['published' => 1]);

        $transition = new Transition('to_published', 'draft', 'published');

        $definition = new Definition(['draft', 'published'], [$transition]);

        $workflow = $this->prophesize(WorkflowInterface::class);
        $workflow
            ->getMarking($object)
            ->shouldBeCalledOnce()
            ->willReturn($marking)
        ;

        $workflow
            ->getDefinition()
            ->shouldBeCalledOnce()
            ->willReturn($definition)
        ;

        $workflow
            ->can(Argument::cetera())
            ->shouldNotBeCalled()
        ;

        $this->workflowRegistry
            ->get($object)
            ->shouldBeCalledOnce()
            ->willReturn($workflow->reveal())
        ;

        $result = $this->normalizer->normalize($object);

        $this->assertSame([], $result['availableStatus']);
        $this->assertSame([], $result['statusBlockerMessage']);
    }

    public function testNormalizeIgnoresNonUnknownBlockers(): void
    {
        $object = new \stdClass();
        $normalizedData = ['id' => 1];

        $this->decorated
            ->normalize($object, null, Argument::type('array'))
            ->shouldBeCalledOnce()
            ->willReturn($normalizedData)
        ;

        $marking = new Marking(['draft' => 1]);

        $transition = new Transition('to_published', 'draft', 'published');

        $definition = new Definition(['draft', 'published'], [$transition]);

        $workflow = $this->prophesize(WorkflowInterface::class);
        $workflow
            ->getMarking($object)
            ->shouldBeCalledOnce()
            ->willReturn($marking)
        ;

        $workflow
            ->getDefinition()
            ->shouldBeCalledOnce()
            ->willReturn($definition)
        ;

        $workflow
            ->can($object, 'to_published')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $blockerList = new TransitionBlockerList([
            new TransitionBlocker('Blocked', TransitionBlocker::BLOCKED_BY_MARKING),
        ]);

        $workflow
            ->buildTransitionBlockerList($object, 'to_published')
            ->shouldBeCalledOnce()
            ->willReturn($blockerList)
        ;

        $this->workflowRegistry
            ->get($object)
            ->shouldBeCalledOnce()
            ->willReturn($workflow->reveal())
        ;

        $result = $this->normalizer->normalize($object);

        $this->assertSame([], $result['availableStatus']);
        $this->assertSame([], $result['statusBlockerMessage']);
    }

    public function testNormalizeHandlesMultipleTargetStates(): void
    {
        $object = new \stdClass();
        $normalizedData = ['id' => 1];

        $this->decorated
            ->normalize($object, null, Argument::type('array'))
            ->shouldBeCalledOnce()
            ->willReturn($normalizedData);

        $marking = new Marking(['draft' => 1]);

        $transition = new Transition('publish', 'draft', ['published', 'live']);

        $definition = new Definition(['draft', 'published', 'live'], [$transition]);

        $workflow = $this->prophesize(WorkflowInterface::class);
        $workflow
            ->getMarking($object)
            ->shouldBeCalledOnce()
            ->willReturn($marking)
        ;

        $workflow
            ->getDefinition()
            ->shouldBeCalledOnce()
            ->willReturn($definition)
        ;

        $workflow
            ->can($object, 'publish')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->workflowRegistry
            ->get($object)
            ->shouldBeCalledOnce()
            ->willReturn($workflow->reveal())
        ;

        $result = $this->normalizer->normalize($object);

        $this->assertSame(['published', 'live'], $result['availableStatus']);
    }

    public function testNormalizeHandlesMultipleBlockersOnSameTransition(): void
    {
        $object = new \stdClass();
        $normalizedData = ['id' => 1];

        $this->decorated
            ->normalize($object, null, Argument::type('array'))
            ->shouldBeCalledOnce()
            ->willReturn($normalizedData)
        ;

        $marking = new Marking(['draft' => 1]);

        $transition = new Transition('to_published', 'draft', ['published', 'live']);

        $definition = new Definition(['draft', 'published', 'live'], [$transition]);

        $workflow = $this->prophesize(WorkflowInterface::class);
        $workflow
            ->getMarking($object)
            ->shouldBeCalledOnce()
            ->willReturn($marking)
        ;

        $workflow
            ->getDefinition()
            ->shouldBeCalledOnce()
            ->willReturn($definition)
        ;

        $workflow
            ->can($object, 'to_published')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $blockerList = new TransitionBlockerList([
            new TransitionBlocker('Missing permission', TransitionBlocker::UNKNOWN),
            new TransitionBlocker('Invalid data', TransitionBlocker::UNKNOWN),
            new TransitionBlocker('Should be ignored', TransitionBlocker::BLOCKED_BY_MARKING),
        ]);

        $workflow
            ->buildTransitionBlockerList($object, 'to_published')
            ->shouldBeCalledOnce()
            ->willReturn($blockerList)
        ;

        $this->workflowRegistry
            ->get($object)
            ->shouldBeCalledOnce()
            ->willReturn($workflow->reveal())
        ;

        $result = $this->normalizer->normalize($object);

        $this->assertSame([], $result['availableStatus']);
        $this->assertCount(4, $result['statusBlockerMessage']);

        $expectedBlockers = [
            ['from' => 'draft', 'to' => 'published', 'message' => 'Missing permission'],
            ['from' => 'draft', 'to' => 'live', 'message' => 'Missing permission'],
            ['from' => 'draft', 'to' => 'published', 'message' => 'Invalid data'],
            ['from' => 'draft', 'to' => 'live', 'message' => 'Invalid data'],
        ];

        $this->assertSame($expectedBlockers, $result['statusBlockerMessage']);
    }

    public function testNormalizeHandlesBlockersFromDifferentTransitions(): void
    {
        $object = new \stdClass();
        $normalizedData = ['id' => 1];

        $this->decorated
            ->normalize($object, null, Argument::type('array'))
            ->shouldBeCalledOnce()
            ->willReturn($normalizedData);

        $marking = new Marking(['draft' => 1]);

        $transition1 = new Transition('to_published', 'draft', 'published');
        $transition2 = new Transition('to_archived', 'draft', 'archived');
        $transition3 = new Transition('to_review', 'draft', 'review');

        $definition = new Definition(['draft', 'published', 'archived', 'review'], [$transition1, $transition2, $transition3]);

        $workflow = $this->prophesize(WorkflowInterface::class);
        $workflow
            ->getMarking($object)
            ->shouldBeCalledOnce()
            ->willReturn($marking)
        ;

        $workflow
            ->getDefinition()
            ->shouldBeCalledOnce()
            ->willReturn($definition)
        ;

        $workflow
            ->can($object, 'to_published')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $workflow
            ->can($object, 'to_archived')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $workflow
            ->can($object, 'to_review')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $blockerList1 = new TransitionBlockerList([
            new TransitionBlocker('Missing permission for publishing', TransitionBlocker::UNKNOWN),
        ]);

        $blockerList2 = new TransitionBlockerList([
            new TransitionBlocker('Cannot archive draft', TransitionBlocker::UNKNOWN),
            new TransitionBlocker('Missing approval', TransitionBlocker::UNKNOWN),
        ]);

        $workflow
            ->buildTransitionBlockerList($object, 'to_published')
            ->shouldBeCalledOnce()
            ->willReturn($blockerList1)
        ;

        $workflow
            ->buildTransitionBlockerList($object, 'to_archived')
            ->shouldBeCalledOnce()
            ->willReturn($blockerList2)
        ;

        $this->workflowRegistry
            ->get($object)
            ->shouldBeCalledOnce()
            ->willReturn($workflow->reveal())
        ;

        $result = $this->normalizer->normalize($object);

        $this->assertSame(['review'], $result['availableStatus']);
        $this->assertCount(3, $result['statusBlockerMessage']);

        $expectedBlockers = [
            ['from' => 'draft', 'to' => 'published', 'message' => 'Missing permission for publishing'],
            ['from' => 'draft', 'to' => 'archived', 'message' => 'Cannot archive draft'],
            ['from' => 'draft', 'to' => 'archived', 'message' => 'Missing approval'],
        ];

        $this->assertSame($expectedBlockers, $result['statusBlockerMessage']);
    }

    public function testNormalizeHandlesMultipleTargetStatesWithBlockers(): void
    {
        $object = new \stdClass();
        $normalizedData = ['id' => 1];

        $this->decorated
            ->normalize($object, null, Argument::type('array'))
            ->shouldBeCalledOnce()
            ->willReturn($normalizedData)
        ;

        $marking = new Marking(['draft' => 1]);

        $transition = new Transition('publish', 'draft', ['published', 'live', 'active']);

        $definition = new Definition(['draft', 'published', 'live', 'active'], [$transition]);

        $workflow = $this->prophesize(WorkflowInterface::class);
        $workflow
            ->getMarking($object)
            ->shouldBeCalledOnce()
            ->willReturn($marking)
        ;

        $workflow
            ->getDefinition()
            ->shouldBeCalledOnce()
            ->willReturn($definition)
        ;

        $workflow
            ->can($object, 'publish')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $blockerList = new TransitionBlockerList([
            new TransitionBlocker('Content not ready', TransitionBlocker::UNKNOWN),
        ]);

        $workflow
            ->buildTransitionBlockerList($object, 'publish')
            ->shouldBeCalledOnce()
            ->willReturn($blockerList)
        ;

        $this->workflowRegistry
            ->get($object)
            ->shouldBeCalledOnce()
            ->willReturn($workflow->reveal())
        ;

        $result = $this->normalizer->normalize($object);

        $this->assertSame([], $result['availableStatus']);
        $this->assertCount(3, $result['statusBlockerMessage']);

        $expectedBlockers = [
            ['from' => 'draft', 'to' => 'published', 'message' => 'Content not ready'],
            ['from' => 'draft', 'to' => 'live', 'message' => 'Content not ready'],
            ['from' => 'draft', 'to' => 'active', 'message' => 'Content not ready'],
        ];

        $this->assertSame($expectedBlockers, $result['statusBlockerMessage']);
    }
}
