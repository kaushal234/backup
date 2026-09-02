<?php

declare(strict_types=1);

namespace App\Tests\AI\Platform;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Factory\AILogFactory;
use App\AI\Platform\AbstractPlatform;
use App\AI\Platform\Invoker\PlatformInvokerInterface;
use App\AI\Platform\PlatformResult;
use App\Entity\AI\AILog;
use App\Entity\AI\Request as AIRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

final class AbstractPlatformTest extends TestCase
{
    use ProphecyTrait;

    public function testInvokeReturnsResultWithoutLog(): void
    {
        $invoker = $this->prophesize(PlatformInvokerInterface::class);
        $factory = $this->prophesize(AILogFactory::class);
        $iriConverter = $this->prophesize(IriConverterInterface::class);

        $messageBag = new MessageBag(
            Message::forSystem('system'),
            Message::ofUser('hello')
        );

        $request = new AIRequest();
        $factory->createRequest('chat', ['temperature' => 0.2])->willReturn($request)->shouldBeCalledOnce();

        $invoker
            ->invokeAsText(
                'mistral-small-latest',
                Argument::that(static function (mixed $actual) use ($messageBag): bool {
                    self::assertSame($messageBag, $actual);

                    return true;
                })
            )
            ->willReturn('AI response')
            ->shouldBeCalledOnce();

        $factory->createLog(Argument::cetera())->shouldNotBeCalled();
        $iriConverter->getIriFromResource(Argument::any())->shouldNotBeCalled();

        $platform = new TestPlatform($invoker->reveal(), $factory->reveal(), $iriConverter->reveal());

        $result = $platform->callInvoke(messageBag: $messageBag, source: 'chat', options: ['temperature' => 0.2]);

        self::assertSame('AI response', $result->result);
        self::assertNull($result->logIri);
    }

    public function testInvokeCreatesLogAndReturnsLogIri(): void
    {
        $invoker = $this->prophesize(PlatformInvokerInterface::class);
        $factory = $this->prophesize(AILogFactory::class);
        $iriConverter = $this->prophesize(IriConverterInterface::class);

        $messageBag = new MessageBag(
            Message::forSystem('system'),
            Message::ofUser('summarize this')
        );

        $request = new AIRequest();
        $log = new AILog();

        $factory->createRequest('summary', ['foo' => 'bar'])->willReturn($request)->shouldBeCalledOnce();

        $invoker
            ->invokeAsText(
                'custom-model',
                Argument::that(static function (mixed $actual) use ($messageBag): bool {
                    self::assertSame($messageBag, $actual);

                    return true;
                })
            )
            ->willReturn('Generated summary')
            ->shouldBeCalledOnce();

        $factory->createLog($request, 'Generated summary')->willReturn($log)->shouldBeCalledOnce();
        $iriConverter->getIriFromResource($log)->willReturn('/api/ai_logs/123')->shouldBeCalledOnce();
        $platform = new TestPlatform($invoker->reveal(), $factory->reveal(), $iriConverter->reveal());

        $result = $platform->callInvoke(messageBag: $messageBag, source: 'summary', options: ['foo' => 'bar'], model: 'custom-model', createLog: true);

        self::assertSame('Generated summary', $result->result);
        self::assertSame('/api/ai_logs/123', $result->logIri);
    }
}

final readonly class TestPlatform extends AbstractPlatform
{
    public function callInvoke(
        MessageBag $messageBag,
        string $source,
        array $options = [],
        string $model = 'mistral-small-latest',
        bool $createLog = false,
    ): PlatformResult {
        return $this->invoke($messageBag, $source, $options, $model, $createLog);
    }
}
