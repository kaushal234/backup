<?php

declare(strict_types=1);

namespace App\Tests\AI\Platform;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Factory\AILogFactory;
use App\AI\Platform\Invoker\PlatformInvoker;
use App\AI\Platform\TextPlatform;
use App\AI\Prompt\PromptInterface;
use App\Entity\AI\AILog;
use App\Entity\AI\Request as AIRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\AI\Platform\Message\MessageBag;

final class TextPlatformTest extends TestCase
{
    use ProphecyTrait;

    public function testAskReturnsResultWithoutLog(): void
    {
        $invoker = $this->prophesize(PlatformInvoker::class);
        $factory = $this->prophesize(AILogFactory::class);
        $iriConverter = $this->prophesize(IriConverterInterface::class);
        $prompt = $this->prophesize(PromptInterface::class);

        $request = new AIRequest();

        $prompt->instructions()->willReturn('Summarize')->shouldBeCalledOnce();
        $prompt->getSource()->willReturn('summary')->shouldBeCalledOnce();
        $prompt->getOptions()->willReturn(['temperature' => 0.2])->shouldBeCalledOnce();

        $factory->createRequest('summary', ['temperature' => 0.2])->willReturn($request)->shouldBeCalledOnce();

        $invoker
            ->invokeAsText(
                'mistral-small-latest',
                Argument::that(static function (mixed $messageBag): bool {
                    self::assertInstanceOf(MessageBag::class, $messageBag);
                    self::assertCount(2, $messageBag->getMessages());

                    return true;
                })
            )
            ->willReturn('AI response')
            ->shouldBeCalledOnce();

        $factory->createLog(Argument::cetera())->shouldNotBeCalled();
        $iriConverter->getIriFromResource(Argument::any())->shouldNotBeCalled();

        $platform = new TextPlatform($invoker->reveal(), $factory->reveal(), $iriConverter->reveal());

        $result = $platform->ask($prompt->reveal(), 'My text');

        self::assertSame('AI response', $result->result);
        self::assertNull($result->logIri);
    }

    public function testAskCreatesLogAndReturnsLogIri(): void
    {
        $invoker = $this->prophesize(PlatformInvoker::class);
        $factory = $this->prophesize(AILogFactory::class);
        $iriConverter = $this->prophesize(IriConverterInterface::class);
        $prompt = $this->prophesize(PromptInterface::class);

        $request = new AIRequest();
        $log = new AILog();

        $prompt->instructions()->willReturn('Translate')->shouldBeCalledOnce();
        $prompt->getSource()->willReturn('translation')->shouldBeCalledOnce();
        $prompt->getOptions()->willReturn(['locale' => 'en'])->shouldBeCalledOnce();

        $factory->createRequest('translation', ['locale' => 'en'])->willReturn($request)->shouldBeCalledOnce();

        $invoker
            ->invokeAsText(
                'custom-model',
                Argument::that(static function (mixed $messageBag): bool {
                    self::assertInstanceOf(MessageBag::class, $messageBag);
                    self::assertCount(2, $messageBag->getMessages());

                    return true;
                })
            )
            ->willReturn('Translated text')
            ->shouldBeCalledOnce();

        $factory->createLog($request, 'Translated text')->willReturn($log)->shouldBeCalledOnce();

        $iriConverter->getIriFromResource($log)->willReturn('/api/ai_logs/42')->shouldBeCalledOnce();

        $platform = new TextPlatform($invoker->reveal(), $factory->reveal(), $iriConverter->reveal());

        $result = $platform->ask(prompt: $prompt->reveal(), text: 'Bonjour', createLog: true, model: 'custom-model');

        self::assertSame('Translated text', $result->result);
        self::assertSame('/api/ai_logs/42', $result->logIri);
    }
}
