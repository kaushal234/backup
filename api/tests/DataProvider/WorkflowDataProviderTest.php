<?php

declare(strict_types=1);

namespace App\Tests\DataProvider;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use App\DataProvider\WorkflowDataProvider;
use App\Dto\Workflow as WorkflowDto;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

class WorkflowDataProviderTest extends TestCase
{
    use ProphecyTrait;

    public function testGetItem()
    {
        $dummy = new class {
            public string $value = 'bar';
        };

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getResourceFromIri('/foo/12')->willReturn($dummy);

        $workflowProphecy = $this->prophesize(Workflow::class);
        $workflowProphecy->getEnabledTransitions($dummy)->willReturn([
            new Transition('foo', 'buz', ['start', 'middle']),
            new Transition('foo', 'baz', ['middle', 'end']),
        ]);

        $registryProphecy = $this->prophesize(Registry::class);
        $registryProphecy->get($dummy, null)->willReturn($workflowProphecy->reveal());

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Registry::class)->shouldBeCalledTimes(1)->willReturn($registryProphecy->reveal());
        $containerProphecy->get(IriConverterInterface::class)->shouldBeCalledTimes(1)->willReturn($iriConverterProphecy->reveal());

        $provider = new WorkflowDataProvider($containerProphecy->reveal());
        /** @var WorkflowDto $results */
        $results = $provider->provide(new Get(), ['resource' => '/foo/12', 'name' => '']);
        self::assertCount(3, $results->getAvailableStatuses());
        self::assertContains('start', $results->getAvailableStatuses());
        self::assertContains('middle', $results->getAvailableStatuses());
        self::assertContains('end', $results->getAvailableStatuses());
    }

    public function testGetItemEmpty()
    {
        $dummy = new class {
            public string $value = 'bar';
        };

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getResourceFromIri('/foo/12')->willReturn($dummy);

        $workflowProphecy = $this->prophesize(Workflow::class);
        $workflowProphecy->getEnabledTransitions($dummy)->willReturn([]);

        $registryProphecy = $this->prophesize(Registry::class);
        $registryProphecy->get($dummy, null)->willReturn($workflowProphecy->reveal());

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Registry::class)->shouldBeCalledTimes(1)->willReturn($registryProphecy->reveal());
        $containerProphecy->get(IriConverterInterface::class)->shouldBeCalledTimes(1)->willReturn($iriConverterProphecy->reveal());

        $provider = new WorkflowDataProvider($containerProphecy->reveal());
        $result = $provider->provide(new Get(), ['resource' => '/foo/12', 'name' => '']);
        self::assertInstanceOf(WorkflowDto::class, $result);
        self::assertEmpty($result->getAvailableStatuses());
    }

    public function testGetItemWithBadIdentifier()
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getResourceFromIri(Argument::any())->shouldNotBeCalled();

        $registryProphecy = $this->prophesize(Registry::class);
        $registryProphecy->get(Argument::any(), Argument::any())->shouldNotBeCalled();

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Registry::class)->shouldNotBeCalled();
        $containerProphecy->get(IriConverterInterface::class)->shouldNotBeCalled();

        $provider = new WorkflowDataProvider($containerProphecy->reveal());
        self::assertNull($provider->provide(new Get(), ['id' => '/foo/12', 'name' => '']), '`resource` is expected to be part of the identifier');
    }

    public function testGetItemWithBadWorkflow()
    {
        $dummy = new class {
            public string $value = 'bar';
        };

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getResourceFromIri('/foo/12')->willReturn($dummy);

        $registryProphecy = $this->prophesize(Registry::class);
        $registryProphecy->get($dummy, 'bar')->willThrow(new \Exception('bad'));

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Registry::class)->shouldBeCalledTimes(1)->willReturn($registryProphecy->reveal());
        $containerProphecy->get(IriConverterInterface::class)->shouldBeCalledTimes(1)->willReturn($iriConverterProphecy->reveal());

        $provider = new WorkflowDataProvider($containerProphecy->reveal());

        self::assertNull($provider->provide(new Get(), ['resource' => '/foo/12', 'name' => 'bar']));
    }

    public function testGetItemWithBadIri()
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getResourceFromIri('/foo/12')->willThrow(new ItemNotFoundException());

        $registryProphecy = $this->prophesize(Registry::class);
        $registryProphecy->get(Argument::any(), Argument::any())->shouldNotBeCalled();

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Registry::class)->shouldNotBeCalled();
        $containerProphecy->get(IriConverterInterface::class)->shouldBeCalledTimes(1)->willReturn($iriConverterProphecy->reveal());

        $provider = new WorkflowDataProvider($containerProphecy->reveal());

        self::assertNull($provider->provide(new Get(), ['resource' => '/foo/12', 'name' => '']));
    }
}
