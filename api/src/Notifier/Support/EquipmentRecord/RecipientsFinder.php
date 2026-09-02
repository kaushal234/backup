<?php

declare(strict_types=1);

namespace App\Notifier\Support\EquipmentRecord;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Repository\Directory\PeopleRepository;

class RecipientsFinder
{
    private readonly PeopleRepository $peopleRepository;

    public function __construct(PeopleRepository $peopleRepository)
    {
        $this->peopleRepository = $peopleRepository;
    }

    public function findRecipients(EquipmentRecord $equipmentRecord): array
    {
        $equipmentRecordAsm = $equipmentRecord->getBuyer()?->getMainSalesRepresentative()?->asm;
        $recipients = [
            ...$this->peopleRepository->findGroupsMembers(['ROLE_PSM', 'ROLE_PSE', 'ROLE_PSA', 'ROLE_PS', 'ROLE_COO', 'ROLE_QAM', 'ROLE_PLANNER', 'ROLE_QE', 'ROLE_QA'], $equipmentRecord->getManufacturerLocation()),
            ...$this->peopleRepository->findGroupsMembers(['ROLE_SAM'], $equipmentRecord->getSalesOrganisation()),
        ];

        if ($equipmentRecordAsm instanceof People) {
            $recipients[] = $equipmentRecordAsm;
        }

        return array_unique($recipients);
    }

    public function getRecipientsForAlertEstimatedGreenTagDate(EquipmentRecord $equipmentRecord, Location $locationName): array
    {
        $recipients = $this->peopleRepository->findGroupsMembers(['ROLE_TCOO', 'ROLE_GCOO', 'ROLE_CMO']);
        $recipients = [...$recipients, ...$this->peopleRepository->findGroupsMembers(['ROLE_RCEO', 'ROLE_RCOO'], $locationName)];

        $inCopy = [...$this->peopleRepository->findGroupsMembers(['ROLE_SAM', 'ROLE_EVP'], $equipmentRecord->getSalesOrganisation())];

        if ($equipmentRecord->orderFactory?->orderLine?->order?->getAsm()) {
            $inCopy[] = $equipmentRecord->orderFactory->orderLine->order->getAsm();
        }

        if (null !== $equipmentRecord->getBuyer() && null !== $equipmentRecord->getBuyer()->getMainSalesRepresentative()) {
            $inCopy[] = $equipmentRecord->getBuyer()->getMainSalesRepresentative()->asm;
        }
        if (null !== $equipmentRecord->getEndUser() && null !== $equipmentRecord->getEndUser()->getMainSalesRepresentative()) {
            $inCopy[] = $equipmentRecord->getEndUser()->getMainSalesRepresentative()->asm;
        }
        $inCopy = [...$inCopy, ...$this->peopleRepository->findGroupsMembers(['ROLE_COO', 'ROLE_PSM', 'ROLE_PSA', 'ROLE_PSE', 'ROLE_PM'], $locationName)];

        return ['recipients' => $recipients, 'inCopy' => $inCopy];
    }

    public function getRecipientsForShippedDateUpdate(EquipmentRecord $equipmentRecord): array
    {
        $recipients = [...$this->peopleRepository->findGroupsMembers(['ROLE_SAM', 'ROLE_SA'], $equipmentRecord->getSalesOrganisation())];

        return array_unique($recipients);
    }
}
