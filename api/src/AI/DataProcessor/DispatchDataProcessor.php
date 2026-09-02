<?php

declare(strict_types=1);

namespace App\AI\DataProcessor;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\AI\Builder\UserMessageBuilder;
use App\AI\Dto\Dispatch;
use App\AI\Factory\ChatFactoryInterface;
use App\Mercure\Publisher\PublisherInterface;
use App\Repository\AI\AILogRepository;
use Psr\Log\LoggerInterface;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\Component\HttpFoundation\RequestStack;

#[FeatureDoc(path: 'ai-chat.md')]
readonly class DispatchDataProcessor implements ProcessorInterface
{
    public function __construct(
        private ChatFactoryInterface $factory,
        private PublisherInterface $publisher,
        private IriConverterInterface $iriConverter,
        private AILogRepository $repository,
        private RequestStack $requestStack,
        private UserMessageBuilder $userMessageBuilder,
        private LoggerInterface $alviLogger,
    ) {
    }

    /**
     * @param Dispatch $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        $iri = $this->iriConverter->getIriFromResource($data->log);
        $chat = $this->factory->createChat($iri);

        $uploadedFile = $this->requestStack->getMainRequest()?->files->get('file');

        $userMessage = $this->userMessageBuilder->build($data->input, $uploadedFile?->getRealPath());

        $streamedContent = '';
        try {
            foreach ($chat->stream($userMessage) as $delta) {
                if ($delta instanceof TextDelta) {
                    $streamedContent .= $delta->getText();
                    $this->publisher->publish($iri, ['content' => $delta->getText(), 'logIri' => $iri], 'text_delta');
                }
            }
        } catch (\Throwable $exception) {
            $this->alviLogger->error('Chatbot dispatch failed', [
                'input' => $data->input,
                'logIri' => $iri,
                'hasUploadedFile' => null !== $uploadedFile,
                'exception' => $exception,
            ]);

            throw $exception;
        }

        // When a tool call happens mid-stream, symfony/ai-chat's ChatStreamListener does not
        // capture the post-tool sub-stream and persists an empty assistant content. We backfill
        // it here with the text we accumulated from all yielded TextDeltas.
        $this->repository->backfillLastResponseContent($data->log, $streamedContent);

        $this->publisher->publish($iri, ['logIri' => $iri], 'assistant_message_complete');

        // On first request, we generate a summary for the title
        if (1 === $data->log->getRequests()->count()) {
            $this->repository->addTitle($data->log);

            $this->publisher->publish($iri, [], 'conversation_title_updated');
        }

        return null;
    }
}
