<?php

declare(strict_types=1);

namespace App\Tests\AI\Store;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Builder\UserContextBuilder;
use App\AI\Builder\UserMessageBuilder;
use App\AI\Factory\AILogFactory;
use App\AI\Store\AILogStore;
use App\Entity\AI\AIFile;
use App\Entity\AI\AILog;
use App\Entity\AI\Request;
use App\Entity\AI\Response;
use App\Entity\Directory\People;
use App\Repository\AI\AILogRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\AI\Platform\Message\AssistantMessage;
use Symfony\AI\Platform\Message\Content\Text;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\SystemMessage;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\AI\Platform\Result\ToolCall;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\RequestStack;

final class AILogStoreTest extends TestCase
{
    use ProphecyTrait;

    private const string LEGACY_UPLOAD_DIR = '/tmp/uploads';
    private const string LOG_IRI = '/api/ai_logs/1';

    public function testLoadReturnsMessageBagWithSystemPrompt(): void
    {
        $conversation = new AILog();
        $builder = $this->prophesize(UserMessageBuilder::class);
        $store = $this->createStore($conversation, $builder->reveal());

        $messageBag = $store->load();
        $messages = $messageBag->getMessages();

        self::assertInstanceOf(SystemMessage::class, $messages[0]);
    }

    public function testLoadInjectsUserContextWhenConversationHasPeople(): void
    {
        $people = new People();
        $people->setFirstname('Jean');
        $people->setLastname('Dupont');
        $people->setJobTitle('Service Technician');
        $people->setLocale('fr');

        $conversation = new AILog();
        $conversation->people = $people;

        $builder = $this->prophesize(UserMessageBuilder::class);
        $store = $this->createStore($conversation, $builder->reveal());

        $messages = $store->load()->getMessages();

        self::assertInstanceOf(SystemMessage::class, $messages[0]);
        self::assertInstanceOf(SystemMessage::class, $messages[1]);
        self::assertStringContainsString('DUPONT Jean', $messages[1]->getContent());
        self::assertStringContainsString('Service Technician', $messages[1]->getContent());
        self::assertStringContainsString('fr', $messages[1]->getContent());
    }

    public function testLoadBuildsMessagesFromRequests(): void
    {
        $conversation = new AILog();
        $conversation->addRequest($this->createRequest('Hello', 'Hi there!'));
        $conversation->addRequest($this->createRequest('How are you?', 'I am fine.'));

        $builder = $this->prophesize(UserMessageBuilder::class);
        $builder->build('Hello', null)->willReturn(Message::ofUser('Hello'));
        $builder->build('How are you?', null)->willReturn(Message::ofUser('How are you?'));

        $store = $this->createStore($conversation, $builder->reveal());

        $messageBag = $store->load();
        $messages = $messageBag->getMessages();

        // System prompt + 2 user messages + 2 assistant messages = 5
        self::assertCount(5, $messages);
        self::assertInstanceOf(SystemMessage::class, $messages[0]);
        self::assertInstanceOf(UserMessage::class, $messages[1]);
        self::assertInstanceOf(AssistantMessage::class, $messages[2]);
        self::assertInstanceOf(UserMessage::class, $messages[3]);
        self::assertInstanceOf(AssistantMessage::class, $messages[4]);

        self::assertSame('Hello', $messages[1]->asText());
        self::assertSame('Hi there!', $messages[2]->asText());
        self::assertSame('How are you?', $messages[3]->asText());
        self::assertSame('I am fine.', $messages[4]->asText());
    }

    public function testLoadEmbedsExtractedFileContentWhenRequestHasFile(): void
    {
        $conversation = new AILog();

        $fileContent = 'plain text body of the attached file';
        $filePath = self::LEGACY_UPLOAD_DIR.'/test.txt';
        @mkdir(\dirname($filePath), recursive: true);
        file_put_contents($filePath, $fileContent);

        try {
            $request = $this->createRequest('Summarize this file', 'Here is the summary.');
            $file = $this->createAIFile('test.txt', 'text/plain');
            $request->setFile($file);
            $conversation->addRequest($request);

            $builder = $this->prophesize(UserMessageBuilder::class);
            $builder->build('Summarize this file', self::LEGACY_UPLOAD_DIR.'/test.txt')
                ->willReturn(new UserMessage(new Text('Summarize this file'), new Text($fileContent)));

            $store = $this->createStore($conversation, $builder->reveal());

            $messageBag = $store->load();
            $messages = $messageBag->getMessages();

            self::assertInstanceOf(UserMessage::class, $messages[1]);

            $contents = $messages[1]->getContent();
            self::assertCount(2, $contents);
            self::assertInstanceOf(Text::class, $contents[0]);
            self::assertSame('Summarize this file', $contents[0]->getText());
            self::assertInstanceOf(Text::class, $contents[1]);
            self::assertStringContainsString($fileContent, $contents[1]->getText());
        } finally {
            @unlink($filePath);
        }
    }

    public function testLoadWithoutFileDoesNotIncludeDocumentUrl(): void
    {
        $conversation = new AILog();
        $conversation->addRequest($this->createRequest('Hello', 'Hi!'));

        $builder = $this->prophesize(UserMessageBuilder::class);
        $builder->build('Hello', null)->willReturn(Message::ofUser('Hello'));

        $store = $this->createStore($conversation, $builder->reveal());

        $messageBag = $store->load();
        $messages = $messageBag->getMessages();

        self::assertInstanceOf(UserMessage::class, $messages[1]);

        $contents = $messages[1]->getContent();
        self::assertCount(1, $contents);
        self::assertInstanceOf(Text::class, $contents[0]);
    }

    public function testLoadWithMoreThan10RequestsUseSummaryAndLastFour(): void
    {
        $conversation = new AILog();
        $conversation->conversationSummary = 'Previous conversation summary';

        for ($i = 0; $i < 12; ++$i) {
            $conversation->addRequest($this->createRequest("Question $i", "Answer $i"));
        }

        $builder = $this->prophesize(UserMessageBuilder::class);
        $builder->build('Question 8', null)->willReturn(Message::ofUser('Question 8'));
        $builder->build('Question 9', null)->willReturn(Message::ofUser('Question 9'));
        $builder->build('Question 10', null)->willReturn(Message::ofUser('Question 10'));
        $builder->build('Question 11', null)->willReturn(Message::ofUser('Question 11'));

        $store = $this->createStore($conversation, $builder->reveal());

        $messageBag = $store->load();
        $messages = $messageBag->getMessages();

        // System prompt + summary system message + 4 user messages + 4 assistant messages = 10
        self::assertCount(10, $messages);
        self::assertInstanceOf(SystemMessage::class, $messages[0]);
        self::assertInstanceOf(SystemMessage::class, $messages[1]);
        self::assertStringContainsString('Previous conversation summary', $messages[1]->getContent());

        // Last 4 requests are questions 8-11
        self::assertInstanceOf(UserMessage::class, $messages[2]);
        self::assertSame('Question 8', $messages[2]->asText());
    }

    public function testSavePersistsRequestAndResponse(): void
    {
        $conversation = new AILog();

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $factory = $this->prophesize(AILogFactory::class);
        $requestStack = $this->prophesize(RequestStack::class);

        $requestStack->getMainRequest()->willReturn(null);

        $aiRequest = new Request();
        $factory->createRequest(Argument::cetera())->willReturn($aiRequest)->shouldBeCalledOnce();

        $entityManager->persist($conversation)->shouldBeCalledOnce();
        $entityManager->flush()->shouldBeCalledOnce();

        $messageBag = new MessageBag(
            Message::forSystem('System prompt'),
            Message::ofUser('User question'),
            Message::ofAssistant('AI answer'),
        );

        $store = $this->createStoreWithDeps($conversation, $entityManager->reveal(), $factory->reveal(), $requestStack->reveal());

        $store->save($messageBag);

        self::assertSame('AI answer', $aiRequest->response->content);
        self::assertTrue($conversation->getRequests()->contains($aiRequest));
    }

    public function testSaveAttachesFileWhenPresent(): void
    {
        $conversation = new AILog();

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $factory = $this->prophesize(AILogFactory::class);
        $requestStack = $this->prophesize(RequestStack::class);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'fake content');
        $uploadedFile = new UploadedFile($tempFile, 'test.pdf', 'application/pdf', test: true);

        $httpRequest = new HttpRequest(files: ['file' => $uploadedFile]);
        $requestStack->getMainRequest()->willReturn($httpRequest);

        $aiRequest = new Request();
        $factory->createRequest(Argument::cetera())->willReturn($aiRequest)->shouldBeCalledOnce();
        $factory->attachFile($aiRequest, $uploadedFile)->shouldBeCalledOnce();

        $entityManager->persist($conversation)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $messageBag = new MessageBag(
            Message::forSystem('System prompt'),
            Message::ofUser('Analyze this'),
            Message::ofAssistant('Analysis done'),
        );

        $store = $this->createStoreWithDeps($conversation, $entityManager->reveal(), $factory->reveal(), $requestStack->reveal());

        $store->save($messageBag);
    }

    public function testSaveDoesNotAttachFileWhenNone(): void
    {
        $conversation = new AILog();

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $factory = $this->prophesize(AILogFactory::class);
        $requestStack = $this->prophesize(RequestStack::class);

        $httpRequest = new HttpRequest();
        $requestStack->getMainRequest()->willReturn($httpRequest);

        $aiRequest = new Request();
        $factory->createRequest(Argument::cetera())->willReturn($aiRequest)->shouldBeCalledOnce();
        $factory->attachFile(Argument::cetera())->shouldNotBeCalled();

        $entityManager->persist($conversation)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $messageBag = new MessageBag(
            Message::forSystem('System prompt'),
            Message::ofUser('Hello'),
            Message::ofAssistant('Hi!'),
        );

        $store = $this->createStoreWithDeps($conversation, $entityManager->reveal(), $factory->reveal(), $requestStack->reveal());

        $store->save($messageBag);
    }

    public function testSaveStoresOnlyOriginalPromptAndExcludesFileContent(): void
    {
        $conversation = new AILog();

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $factory = $this->prophesize(AILogFactory::class);
        $requestStack = $this->prophesize(RequestStack::class);

        $requestStack->getMainRequest()->willReturn(null);

        $aiRequest = new Request();
        $capturedInput = null;
        $factory
            ->createRequest(Argument::cetera())
            ->will(static function (array $args) use ($aiRequest, &$capturedInput): Request {
                $capturedInput = $args[3] ?? null;

                return $aiRequest;
            })
            ->shouldBeCalledOnce();

        $entityManager->persist(Argument::any())->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $userMessage = new UserMessage(
            new Text('Summarize this'),
            new Text('[Attached file content] huge extracted body that must not be persisted'),
        );

        $messageBag = new MessageBag(
            Message::forSystem('System prompt'),
            $userMessage,
            Message::ofAssistant('Summary done'),
        );

        $store = $this->createStoreWithDeps($conversation, $entityManager->reveal(), $factory->reveal(), $requestStack->reveal());

        $store->save($messageBag);

        self::assertSame('Summarize this', $capturedInput);
    }

    public function testSaveExtractsToolCallNameAsSource(): void
    {
        $conversation = new AILog();

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $factory = $this->prophesize(AILogFactory::class);
        $requestStack = $this->prophesize(RequestStack::class);

        $requestStack->getMainRequest()->willReturn(null);

        $aiRequest = new Request();
        $factory->createRequest(Argument::cetera())->willReturn($aiRequest)->shouldBeCalledOnce();

        $entityManager->persist(Argument::any())->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $toolCall = new ToolCall('call_1', 'search_tool', ['query' => 'test']);
        $messageBag = new MessageBag(
            Message::forSystem('System prompt'),
            Message::ofUser('Find something'),
            Message::ofAssistant('Result', $toolCall),
        );

        $store = $this->createStoreWithDeps($conversation, $entityManager->reveal(), $factory->reveal(), $requestStack->reveal());

        $store->save($messageBag);
    }

    private function createStore(AILog $conversation, UserMessageBuilder $userMessageBuilder): AILogStore
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $factory = $this->prophesize(AILogFactory::class);
        $requestStack = $this->prophesize(RequestStack::class);

        $repository = $this->prophesize(AILogRepository::class);
        $repository->summarizePreviousRequests($conversation)->willReturn($conversation);
        $entityManager->getRepository(AILog::class)->willReturn($repository->reveal());

        $iriConverter = $this->prophesize(IriConverterInterface::class);
        $iriConverter->getResourceFromIri(self::LOG_IRI)->willReturn($conversation);

        return new AILogStore(
            entityManager: $entityManager->reveal(),
            factory: $factory->reveal(),
            iriConverter: $iriConverter->reveal(),
            requestStack: $requestStack->reveal(),
            userMessageBuilder: $userMessageBuilder,
            userContextBuilder: new UserContextBuilder(),
            legacyUploadDir: self::LEGACY_UPLOAD_DIR,
            logIri: self::LOG_IRI,
        );
    }

    private function createStoreWithDeps(
        AILog $conversation,
        EntityManagerInterface $entityManager,
        AILogFactory $factory,
        RequestStack $requestStack,
    ): AILogStore {
        $repository = $this->prophesize(AILogRepository::class);
        $repository->summarizePreviousRequests($conversation)->willReturn($conversation);

        $iriConverter = $this->prophesize(IriConverterInterface::class);
        $iriConverter->getResourceFromIri(self::LOG_IRI)->willReturn($conversation);

        $userMessageBuilder = $this->prophesize(UserMessageBuilder::class);

        return new AILogStore(
            entityManager: $entityManager,
            factory: $factory,
            iriConverter: $iriConverter->reveal(),
            requestStack: $requestStack,
            userMessageBuilder: $userMessageBuilder->reveal(),
            userContextBuilder: new UserContextBuilder(),
            legacyUploadDir: self::LEGACY_UPLOAD_DIR,
            logIri: self::LOG_IRI,
        );
    }

    private function createRequest(string $content, string $responseContent): Request
    {
        $request = new Request();
        $request->content = $content;

        $response = new Response();
        $response->content = $responseContent;
        $request->response = $response;

        return $request;
    }

    private function createAIFile(string $filePath, string $mimeType): AIFile
    {
        $file = new AIFile();
        $file->setFilePath($filePath);
        $file->setMimeType($mimeType);

        return $file;
    }
}
