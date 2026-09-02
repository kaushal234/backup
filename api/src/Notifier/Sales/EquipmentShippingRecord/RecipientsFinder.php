<?php

declare(strict_types=1);

namespace App\Notifier\Sales\EquipmentShippingRecord;

use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\Repository\Directory\PeopleRepository;

class RecipientsFinder
{
    public function __construct(
        private readonly PeopleRepository $peopleRepository,
    ) {
    }

    /**
     * one EquipmentShippingRecordLine (pick-up change).
     */
    public function findForPickUpChanges(EquipmentShippingRecordLine $line): array
    {
        return [
            ...$this->peopleRepository->findGroupsMembers(['ROLE_SA'], $line->equipmentShippingRecord->sso),
            ...$this->peopleRepository->findGroupsMembers(['ROLE_PSA'], $line->equipmentRecord->getManufacturerLocation()),
        ];
    }

    /**
     * full EquipmentShippingRecord (new record notification).
     */
    public function findForNewRecord(EquipmentShippingRecord $record): array
    {
        $recipients = [...$this->peopleRepository->findGroupsMembers(['ROLE_SA'], $record->sso)];

        foreach ($record->getEquipmentShippingRecordLines() as $line) {
            $recipients = [
                ...$recipients,
                ...$this->peopleRepository->findGroupsMembers(['ROLE_PSA'], $line->equipmentRecord->getManufacturerLocation()),
            ];
        }

        return $recipients;
    }
}
