<?php

declare(strict_types=1);

namespace App\DataProvider\DocumentTranslation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\DocumentTranslation;
use App\Http\DeeplClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentTranslationDownloadDataProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')] private ProviderInterface $itemProvider,
        private readonly DeeplClient $deeplClient,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object
    {
        $documentTranslation = $this->itemProvider->provide($operation, $uriVariables, $context);

        if (!$documentTranslation instanceof DocumentTranslation) {
            throw new NotFoundHttpException('Document not found.');
        }

        if (DocumentTranslation::STATUS_READY !== $documentTranslation->status) {
            throw new BadRequestHttpException(\sprintf('Document is not in status ready (%s).', $documentTranslation->status));
        }

        $response = $this->deeplClient->downloadDocument(
            $documentTranslation->documentId,
            $documentTranslation->documentKey
        );

        $this->entityManager->remove($documentTranslation);
        $this->entityManager->flush();

        return $response;
    }
}
