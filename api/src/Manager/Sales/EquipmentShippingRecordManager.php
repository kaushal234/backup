<?php

declare(strict_types=1);

namespace App\Manager\Sales;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\Entity\Sales\Incoterm;
use App\Notifier\Support\EquipmentRecord\EquipmentRecordNotifier;
use App\Repository\Sales\EquipmentShippingRecordLineRepository;
use Doctrine\ORM\EntityManagerInterface;

class EquipmentShippingRecordManager
{
    public function __construct(
        private readonly EquipmentShippingRecordLineRepository $equipmentShippingRecordLineRepository,
        private readonly EquipmentRecordNotifier $notifier,
        private readonly EntityManagerInterface $entityManager,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function processDateShippedChangedForEquipmentShippingRecord(EquipmentRecord $equipmentRecord, ?string $previousDateShipped): void
    {
        $equipmentShippingRecordLine = $this->equipmentShippingRecordLineRepository->findLastEquipmentShippingRecordLineByEquipmentRecord($equipmentRecord);
        if (null === $equipmentShippingRecordLine) {
            return;
        }

        $equipmentShippingRecord = $equipmentShippingRecordLine->equipmentShippingRecord;
        if (\in_array($equipmentShippingRecord->incoterm->code, [Incoterm::EXW, Incoterm::FCA], true)) {
            $status = EquipmentShippingRecord::CLOSED;
            $this->notifier->sendShippedDateNotification($equipmentRecord, $previousDateShipped);
        } else {
            $status = EquipmentShippingRecord::SHIPPED;
        }

        /** @var EquipmentShippingRecordLine $line */
        foreach ($equipmentShippingRecord->getEquipmentShippingRecordLines() as $line) {
            if (null === $line->equipmentRecord->getDateShipped()) {
                return;
            }
        }
        $equipmentShippingRecord->setStatus($status);

        $comment =
            (new Comment())
                ->setMessage(\sprintf(
                    'This ESR has been automatically %s. When the shipping date of ER#%s has been updated',
                    $status,
                    $equipmentShippingRecordLine->equipmentRecord->getLegacyId(),
                ))
                ->setResource($this->iriConverter->getIriFromResource($equipmentShippingRecord))
        ;

        $this->entityManager->persist($comment);
        $this->entityManager->persist($equipmentShippingRecord);
        $this->entityManager->flush();
    }
}
