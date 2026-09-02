<?php

declare(strict_types=1);

namespace App\Tests\AI\DataProcessor;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use App\AI\Builder\UserMessageBuilder;
use App\AI\DataProcessor\DispatchDataProcessor;
use App\AI\Dto\Dispatch;
use App\AI\Factory\ChatFactoryInterface;
use App\Entity\AI\AILog;
use App\Entity\AI\Request;
use App\Mercure\Publisher\PublisherInterface;
use App\Repository\AI\AILogRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\AI\Chat\ChatInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\RequestStack;

final class DispatchDataProcessorTest extends TestCase
{
    public function testItPublishesAssistantMessageAndUpdatesTitleForFirstRequest(): void
    {
        $log = (new AILog())->addRequest(new Request());

        $data = new Dispatch();
        $data->log = $log;
        $data->input = 'Hello';

        $chat = $this->createMock(ChatInterface::class);
        $chat
            ->expects(self::once())
            ->method('stream')
            ->with(self::isInstanceOf(UserMessage::class))
            ->willReturnCallback(static function (): \Generator {
                yield new TextDelta('AI response');
            });

        $userMessageBuilder = $this->createMock(UserMessageBuilder::class);
        $userMessageBuilder->expects(self::once())
            ->method('build')
            ->with('Hello', null)
            ->willReturn(Message::ofUser('Hello'));

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->expects(self::once())
            ->method('getIriFromResource')
            ->with($log)
            ->willReturn('/api/ai_logs/1');

        $factory = $this->createMock(ChatFactoryInterface::class);
        $factory->expects(self::once())
            ->method('createChat')
            ->with('/api/ai_logs/1')
            ->willReturn($chat);

        $repository = $this->createMock(AILogRepository::class);
        $repository->expects(self::once())->method('addTitle')->with($log);

        $publisher = $this->createMock(PublisherInterface::class);
        $publisher->expects(self::exactly(3))
            ->method('publish')
            ->withConsecutive(
                ['/api/ai_logs/1', ['content' => 'AI response', 'logIri' => '/api/ai_logs/1'], 'text_delta'],
                ['/api/ai_logs/1', ['logIri' => '/api/ai_logs/1'], 'assistant_message_complete'],
                ['/api/ai_logs/1', [], 'conversation_title_updated'],
            );

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects(self::once())->method('getMainRequest')->willReturn(null);

        $processor = new DispatchDataProcessor(
            $factory,
            $publisher,
            $iriConverter,
            $repository,
            $requestStack,
            $userMessageBuilder,
            new NullLogger(),
        );

        self::assertNull($processor->process($data, $this->createMock(Operation::class)));
    }

    public function testItPublishesAssistantMessageWithoutUpdatingTitleWhenRequestIsNotFirst(): void
    {
        $log = (new AILog())->addRequest(new Request())->addRequest(new Request());

        $data = new Dispatch();
        $data->log = $log;
        $data->input = 'Hello';

        $chat = $this->createMock(ChatInterface::class);
        $chat
            ->expects(self::once())
            ->method('stream')
            ->with(self::isInstanceOf(UserMessage::class))
            ->willReturnCallback(static function (): \Generator {
                yield new TextDelta('AI response');
            });

        $userMessageBuilder = $this->createMock(UserMessageBuilder::class);
        $userMessageBuilder->expects(self::once())
            ->method('build')
            ->with('Hello', null)
            ->willReturn(Message::ofUser('Hello'));

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->expects(self::once())
            ->method('getIriFromResource')
            ->with($log)
            ->willReturn('/api/ai_logs/1');

        $factory = $this->createMock(ChatFactoryInterface::class);
        $factory->expects(self::once())
            ->method('createChat')
            ->with('/api/ai_logs/1')
            ->willReturn($chat);

        $repository = $this->createMock(AILogRepository::class);
        $repository->expects(self::never())->method('addTitle');

        $publisher = $this->createMock(PublisherInterface::class);
        $publisher->expects(self::exactly(2))
            ->method('publish')
            ->withConsecutive(
                ['/api/ai_logs/1', ['content' => 'AI response', 'logIri' => '/api/ai_logs/1'], 'text_delta'],
                ['/api/ai_logs/1', ['logIri' => '/api/ai_logs/1'], 'assistant_message_complete'],
            );

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects(self::once())->method('getMainRequest')->willReturn(null);

        $processor = new DispatchDataProcessor(
            $factory,
            $publisher,
            $iriConverter,
            $repository,
            $requestStack,
            $userMessageBuilder,
            new NullLogger(),
        );

        self::assertNull($processor->process($data, $this->createMock(Operation::class)));
    }

    public function testItIncludesUploadedFileInUserMessage(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'file content');
        $uploadedFile = new UploadedFile($tempFile, 'doc.pdf', 'application/pdf', test: true);

        $log = (new AILog())->addRequest(new Request());

        $data = new Dispatch();
        $data->log = $log;
        $data->input = 'Analyze this';

        $chat = $this->createMock(ChatInterface::class);
        $chat
            ->expects(self::once())
            ->method('stream')
            ->with(self::isInstanceOf(UserMessage::class))
            ->willReturnCallback(static function (): \Generator {
                yield new TextDelta('Analysis done');
            });

        $userMessageBuilder = $this->createMock(UserMessageBuilder::class);
        $userMessageBuilder->expects(self::once())
            ->method('build')
            ->with('Analyze this', $tempFile)
            ->willReturn(Message::ofUser('Analyze this'));

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->expects(self::once())
            ->method('getIriFromResource')
            ->with($log)
            ->willReturn('/api/ai_logs/1');

        $factory = $this->createMock(ChatFactoryInterface::class);
        $factory->expects(self::once())
            ->method('createChat')
            ->with('/api/ai_logs/1')
            ->willReturn($chat);

        $repository = $this->createMock(AILogRepository::class);
        $repository->expects(self::once())->method('addTitle')->with($log);

        $publisher = $this->createMock(PublisherInterface::class);
        $publisher->expects(self::exactly(3))
            ->method('publish')
            ->withConsecutive(
                ['/api/ai_logs/1', ['content' => 'Analysis done', 'logIri' => '/api/ai_logs/1'], 'text_delta'],
                ['/api/ai_logs/1', ['logIri' => '/api/ai_logs/1'], 'assistant_message_complete'],
                ['/api/ai_logs/1', [], 'conversation_title_updated'],
            );

        $httpRequest = new HttpRequest(files: ['file' => $uploadedFile]);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects(self::once())->method('getMainRequest')->willReturn($httpRequest);

        $processor = new DispatchDataProcessor(
            $factory,
            $publisher,
            $iriConverter,
            $repository,
            $requestStack,
            $userMessageBuilder,
            new NullLogger(),
        );

        try {
            self::assertNull($processor->process($data, $this->createMock(Operation::class)));
        } finally {
            @unlink($tempFile);
        }
    }

    public function testItLogsAndRethrowsWhenChatStreamFails(): void
    {
        $log = (new AILog())->addRequest(new Request());

        $data = new Dispatch();
        $data->log = $log;
        $data->input = 'Hello';

        $exception = new \RuntimeException('LLM unavailable');

        $chat = $this->createMock(ChatInterface::class);
        $chat
            ->expects(self::once())
            ->method('stream')
            ->with(self::isInstanceOf(UserMessage::class))
            ->willThrowException($exception);

        $userMessageBuilder = $this->createMock(UserMessageBuilder::class);
        $userMessageBuilder->expects(self::once())
            ->method('build')
            ->with('Hello', null)
            ->willReturn(Message::ofUser('Hello'));

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->expects(self::once())
            ->method('getIriFromResource')
            ->with($log)
            ->willReturn('/api/ai_logs/1');

        $factory = $this->createMock(ChatFactoryInterface::class);
        $factory->expects(self::once())
            ->method('createChat')
            ->with('/api/ai_logs/1')
            ->willReturn($chat);

        $repository = $this->createMock(AILogRepository::class);
        $repository->expects(self::never())->method('addTitle');

        $publisher = $this->createMock(PublisherInterface::class);
        $publisher->expects(self::never())->method('publish');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects(self::once())->method('getMainRequest')->willReturn(null);

        $alviLogger = $this->createMock(LoggerInterface::class);
        $alviLogger
            ->expects(self::once())
            ->method('error')
            ->with(
                'Chatbot dispatch failed',
                self::callback(static function (array $context) use ($exception): bool {
                    return 'Hello' === $context['input']
                        && '/api/ai_logs/1' === $context['logIri']
                        && false === $context['hasUploadedFile']
                        && $exception === $context['exception'];
                }),
            );

        $processor = new DispatchDataProcessor(
            $factory,
            $publisher,
            $iriConverter,
            $repository,
            $requestStack,
            $userMessageBuilder,
            $alviLogger,
        );

        $this->expectExceptionObject($exception);
        $processor->process($data, $this->createMock(Operation::class));
    }
}
