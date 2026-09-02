<?php

declare(strict_types=1);

namespace App\DataProvider\DocumentTranslation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\DocumentTranslation\DeeplStatusResponse;
use App\Entity\DocumentTranslation;
use App\Http\DeeplClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class DocumentTranslationRefreshDataProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')] private ProviderInterface $itemProvider,
        private readonly DeeplClient $deeplClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly DenormalizerInterface $serializer,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): DocumentTranslation
    {
        $documentTranslation = $this->itemProvider->provide($operation, $uriVariables, $context);

        if (!$documentTranslation instanceof DocumentTranslation) {
            throw new NotFoundHttpException('DocumentTranslation not found.');
        }

        if (!$documentTranslation->documentId || !$documentTranslation->documentKey) {
            throw new BadRequestHttpException('Missing DeepL identifiers on this translation.');
        }

        $statusPayload = $this->deeplClient->getDocumentStatus($documentTranslation->documentId, $documentTranslation->documentKey);

        /** @var DeeplStatusResponse $deeplStatusResponse */
        $deeplStatusResponse = $this->serializer->denormalize($statusPayload, DeeplStatusResponse::class);

        if ($deeplStatusResponse->isDone()) {
            $documentTranslation->status = DocumentTranslation::STATUS_READY;
            $documentTranslation->estimatedSeconds = 0;
            $documentTranslation->errorMessage = null;
        } elseif ($deeplStatusResponse->isError()) {
            $documentTranslation->status = DocumentTranslation::STATUS_FAILED;
            $documentTranslation->errorMessage = $deeplStatusResponse->message ?? 'DeepL returned error.';
            $documentTranslation->estimatedSeconds = null;
        } else {
            $documentTranslation->status = DocumentTranslation::STATUS_QUEUED;
            if (null !== $deeplStatusResponse->secondsRemaining) {
                $documentTranslation->estimatedSeconds = $deeplStatusResponse->secondsRemaining;
            }
        }

        $this->entityManager->flush();

        return $documentTranslation;
    }
}
