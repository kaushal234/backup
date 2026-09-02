<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DocumentTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DocumentTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentTranslation::class);
    }

    public function findDocumentTranslationToRemove(): array
    {
        return $this->createQueryBuilder('dt')
            ->where("DATE_ADD(dt.createdAt, 3, 'DAY') < :now")
            ->setParameter('now', new \DateTime('now'))
            ->orderBy('dt.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function updateDocumentTranslationBeforeSendToDeepl(DocumentTranslation $documentTranslation, $file): DocumentTranslation
    {
        $documentTranslation->filename = $file->getClientOriginalName() ?: $file->getFilename();
        $documentTranslation->mimeType = $file->getMimeType() ?? $file->getClientMimeType();
        $size = $file->getSize();
        $documentTranslation->size = false !== $size ? $size : null;
        $documentTranslation->status = DocumentTranslation::STATUS_QUEUED;

        return $documentTranslation;
    }

    public function updateDocumentTranslationWithDeepLResponse(DocumentTranslation $documentTranslation, array $payload): DocumentTranslation
    {
        $documentTranslation->documentId = $payload['document_id'] ?? null;
        $documentTranslation->documentKey = $payload['document_key'] ?? null;
        $documentTranslation->estimatedSeconds = \array_key_exists('estimated_seconds', $payload)
            ? (int) $payload['estimated_seconds']
            : null;

        return $documentTranslation;
    }
}
