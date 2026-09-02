<?php

declare(strict_types=1);

namespace App\AI\Store;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Builder\UserContextBuilder;
use App\AI\Builder\UserMessageBuilder;
use App\AI\Factory\AILogFactory;
use App\Entity\AI\AILog;
use App\Entity\AI\Request;
use App\Entity\AI\Response;
use App\Repository\AI\AILogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\AI\Chat\ManagedStoreInterface;
use Symfony\AI\Chat\MessageStoreInterface;
use Symfony\AI\Platform\Message\AssistantMessage;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class AILogStore implements MessageStoreInterface, ManagedStoreInterface
{
    private AILog $conversation;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AILogFactory $factory,
        private IriConverterInterface $iriConverter,
        private RequestStack $requestStack,
        private UserMessageBuilder $userMessageBuilder,
        private UserContextBuilder $userContextBuilder,
        private string $legacyUploadDir,
        private string $logIri,
    ) {
        /** @var AILog $conversation */
        $conversation = $this->iriConverter->getResourceFromIri($this->logIri);
        $this->conversation = $conversation;
    }

    public function setup(array $options = []): void
    {
        // not needed
    }

    public function save(MessageBag $messages): void
    {
        /**
         * @var AssistantMessage|null $lastAssistantMessage
         * @var UserMessage|null      $lastUserMessage
         */
        [$lastUserMessage, $lastAssistantMessage] = $this->lastTurn($messages);

        $source = null;
        if ($lastAssistantMessage->hasToolCalls()) {
            foreach ($lastAssistantMessage->getToolCalls() as $toolCall) {
                if (null !== $source) {
                    continue;
                }

                $source = $toolCall->getName();
            }
        }

        $uploadedFile = $this->requestStack->getMainRequest()?->files->get('file');

        $request = $this->factory->createRequest(url: $source ?? 'chat', input: UserMessageBuilder::extractPrompt($lastUserMessage));
        $response = new Response();
        $response->content = $lastAssistantMessage->asText() ?? '';

        $request->response = $response;

        $this->conversation->addRequest($request);

        $this->entityManager->persist($this->conversation);
        $this->entityManager->flush();

        if (null !== $uploadedFile) {
            $this->factory->attachFile($request, $uploadedFile);
        }
    }

    public function load(): MessageBag
    {
        $messageBag = new MessageBag();
        $messageBag->add(Message::forSystem(<<<'PROMPT'
            You are an expert AI assistant.

            You can call tools when useful.
            If no tool is relevant, answer normally.
            When calling a tool, provide valid arguments.
            If multiple tools could match, ask a clarifying question instead of calling a tool.
            Use code blocks (```) ONLY for actual code.
            Do not use indentation for normal text.
            If you have any link in the results with an id, provide it in the answer (only if you are sure of the link).
            PROMPT));

        $userContext = isset($this->conversation->people)
            ? $this->userContextBuilder->build($this->conversation->people)
            : null;
        if (null !== $userContext) {
            $messageBag->add(Message::forSystem($userContext));
        }

        /** @var AILogRepository $repository */
        $repository = $this->entityManager->getRepository(AILog::class);

        $conversation = $repository->summarizePreviousRequests($this->conversation);

        $requests = $conversation->getRequests();
        $totalRequests = $requests->count();

        if ($totalRequests < 10) {
            foreach ($requests as $request) {
                $messageBag->add($this->buildUserMessage($request));

                if (!empty($request->response->content)) {
                    $messageBag->add(Message::ofAssistant($request->response->content));
                }
            }
        } else {
            if (!empty($conversation->conversationSummary)) {
                $messageBag->add(Message::forSystem(\sprintf("Summary of the previous conversation:\n%s", $conversation->conversationSummary)));
            }

            $lastRequests = $requests->slice(-4);
            foreach ($lastRequests as $request) {
                $messageBag->add($this->buildUserMessage($request));

                if (!empty($request->response->content)) {
                    $messageBag->add(Message::ofAssistant($request->response->content));
                }
            }
        }

        return $messageBag;
    }

    public function drop(): void
    {
        // not needed
    }

    private function buildUserMessage(Request $request): UserMessage
    {
        $file = $request->getFile();
        $filepath = null !== $file ? \sprintf('%s/%s', $this->legacyUploadDir, $file->getFilePath()) : null;

        return $this->userMessageBuilder->build($request->content, $filepath);
    }

    /**
     * @return array{0: UserMessage|null, 1: AssistantMessage|null}
     */
    private function lastTurn(MessageBag $messageBag): array
    {
        $messages = $messageBag->getMessages();

        $lastUser = null;
        $lastAssistant = null;

        for ($i = \count($messages) - 1; $i >= 0; --$i) {
            if (null === $lastAssistant && $messages[$i] instanceof AssistantMessage) {
                $lastAssistant = $messages[$i];
                continue;
            }
            if ($messages[$i] instanceof UserMessage) {
                $lastUser = $messages[$i];
                break;
            }
        }

        return [$lastUser, $lastAssistant];
    }
}
