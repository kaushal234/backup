<?php

declare(strict_types=1);

namespace App\Tests\AI\Platform;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Factory\AILogFactory;
use App\AI\Platform\DocumentPlatform;
use App\AI\Platform\Invoker\PlatformInvoker;
use App\AI\Prompt\PromptInterface;
use App\AI\Service\FileTextExtractor;
use App\Entity\AI\AILog;
use App\Entity\AI\Request as AIRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\AI\Platform\Message\MessageBag;

final class DocumentPlatformTest extends TestCase
{
    use ProphecyTrait;

    public function testAskReturnsResultWithoutLog(): void
    {
        $invoker = $this->prophesize(PlatformInvoker::class);
        $factory = $this->prophesize(AILogFactory::class);
        $iriConverter = $this->prophesize(IriConverterInterface::class);
        $prompt = $this->prophesize(PromptInterface::class);
        $extractor = $this->prophesize(FileTextExtractor::class);

        $request = new AIRequest();

        $prompt->instructions()->willReturn('Analyze this document')->shouldBeCalledOnce();
        $prompt->getSource()->willReturn('document-analysis')->shouldBeCalledOnce();
        $prompt->getOptions()->willReturn(['temperature' => 0.1])->shouldBeCalledOnce();

        $factory->createRequest('document-analysis', ['temperature' => 0.1])->willReturn($request)->shouldBeCalledOnce();

        $extractor->extract(Argument::any())->willReturn('extracted text');

        $invoker
            ->invokeAsText(
                'mistral-small-latest',
                Argument::that(static function (mixed $messageBag): bool {
                    self::assertInstanceOf(MessageBag::class, $messageBag);
                    self::assertCount(1, $messageBag->getMessages());

                    return true;
                })
            )
            ->willReturn('Document summary')
            ->shouldBeCalledOnce();

        $factory->createLog(Argument::cetera())->shouldNotBeCalled();
        $iriConverter->getIriFromResource(Argument::any())->shouldNotBeCalled();

        $platform = new DocumentPlatform($invoker->reveal(), $factory->reveal(), $iriConverter->reveal(), $extractor->reveal());

        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'fake document content');

        try {
            $result = $platform->ask(
                prompt: $prompt->reveal(),
                filepath: $tempFile,
            );
        } finally {
            @unlink($tempFile);
        }

        self::assertSame('Document summary', $result->result);
        self::assertNull($result->logIri);
    }

    public function testAskCreatesLogAndReturnsLogIri(): void
    {
        $invoker = $this->prophesize(PlatformInvoker::class);
        $factory = $this->prophesize(AILogFactory::class);
        $iriConverter = $this->prophesize(IriConverterInterface::class);
        $prompt = $this->prophesize(PromptInterface::class);
        $extractor = $this->prophesize(FileTextExtractor::class);

        $request = new AIRequest();
        $log = new AILog();

        $prompt->instructions()->willReturn('Extract key points')->shouldBeCalledOnce();
        $prompt->getSource()->willReturn('document-extract')->shouldBeCalledOnce();
        $prompt->getOptions()->willReturn(['locale' => 'fr'])->shouldBeCalledOnce();

        $factory->createRequest('document-extract', ['locale' => 'fr'])->willReturn($request)->shouldBeCalledOnce();

        $extractor->extract(Argument::any())->willReturn('extracted text');

        $invoker
            ->invokeAsText(
                'custom-model',
                Argument::that(static function (mixed $messageBag): bool {
                    self::assertInstanceOf(MessageBag::class, $messageBag);
                    self::assertCount(1, $messageBag->getMessages());

                    return true;
                })
            )
            ->willReturn('Extracted content')
            ->shouldBeCalledOnce();

        $factory->createLog($request, 'Extracted content')->willReturn($log)->shouldBeCalledOnce();

        $iriConverter->getIriFromResource($log)->willReturn('/api/ai_logs/77')->shouldBeCalledOnce();

        $platform = new DocumentPlatform($invoker->reveal(), $factory->reveal(), $iriConverter->reveal(), $extractor->reveal());

        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'fake document content');

        try {
            $result = $platform->ask(
                prompt: $prompt->reveal(),
                filepath: $tempFile,
                createLog: true,
                model: 'custom-model',
            );
        } finally {
            @unlink($tempFile);
        }

        self::assertSame('Extracted content', $result->result);
        self::assertSame('/api/ai_logs/77', $result->logIri);
    }
}
