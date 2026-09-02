<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Support\EquipmentRecordPublic\EquipmentRecordPublic;
use App\Dto\Support\EquipmentRecordPublic\ManualDocumentPublic;
use App\Dto\Support\EquipmentRecordPublic\ManualPublic;
use App\Entity\EquipmentRecord;
use App\Entity\Support\Manual;
use App\Entity\Support\ManualDocument;
use Doctrine\ORM\EntityManagerInterface;

class EquipmentRecordPublicProvider implements ProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?EquipmentRecordPublic
    {
        $serialNumber = $uriVariables['serialNumber'] ?? null;

        if (null === $serialNumber) {
            return null;
        }

        /** @var EquipmentRecord|null $equipmentRecord */
        $equipmentRecord = $this->entityManager->getRepository(EquipmentRecord::class)
            ->createQueryBuilder('er')
            ->where('TRIM(er.serialNumber) = :sn')
            ->setParameter('sn', mb_trim($serialNumber))
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null === $equipmentRecord) {
            return null;
        }

        $dto = new EquipmentRecordPublic();
        $dto->id = $equipmentRecord->getId();
        $dto->serialNumber = $equipmentRecord->getSerialNumber();
        $dto->model = $equipmentRecord->getModel();
        $dto->type = $equipmentRecord->getType();
        $dto->optionsDescription = $equipmentRecord->getOptionsDescription();

        $airport = $equipmentRecord->getAirport();
        $dto->airportCode = $airport?->getCode();

        // Manuals: filter released
        /** @var Manual[] $manuals */
        $manuals = array_filter(
            $equipmentRecord->getManuals()->toArray(),
            static fn (Manual $manual): bool => ManualPublic::STATUS_RELEASED === $manual->status
                && null !== $manual->createdAt
        );

        if ([] === $manuals) {
            return $dto;
        }
        // Manuals sort by createdAt DESC
        usort(
            $manuals,
            static fn (Manual $a, Manual $b): int => $b->createdAt <=> $a->createdAt
        );

        $lastManual = $manuals[0];

        $lastManualPublic = new ManualPublic();
        $lastManualPublic->id = $lastManual->getId();
        $lastManualPublic->createdAt = $lastManual->createdAt->format(\DATE_ATOM);
        $lastManualPublic->documents = [];

        /** @var ManualDocument $document */
        foreach ($lastManual->getDocuments() as $document) {
            $category = $document->category;
            if (null === $category) {
                continue;
            }

            $categoryName = $category->name;
            if (!\in_array($categoryName, ManualDocumentPublic::PUBLIC_CATEGORY_NAMES, true)) {
                continue;
            }

            $manualDocumentPublic = new ManualDocumentPublic();
            $manualDocumentPublic->id = $document->getId();
            $manualDocumentPublic->categoryName = $categoryName;
            $manualDocumentPublic->description = $document->description;

            $manualDocumentFile = $document->getDocument();
            if (null !== $manualDocumentFile) {
                $manualDocumentPublic->fileId = $manualDocumentFile->getId();
                $manualDocumentPublic->extension = $manualDocumentFile->getExtension();
            }

            $lastManualPublic->documents[] = $manualDocumentPublic;
        }

        $dto->lastManual = $lastManualPublic;

        return $dto;
    }
}
