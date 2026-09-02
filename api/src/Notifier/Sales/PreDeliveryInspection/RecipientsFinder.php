<?php

declare(strict_types=1);

namespace App\Notifier\Sales\PreDeliveryInspection;

use App\Entity\Directory\People;
use App\Entity\Sales\PreDeliveryInspection;
use App\Repository\Directory\PeopleRepository;

class RecipientsFinder
{
    private PeopleRepository $peopleRepository;

    public function __construct(PeopleRepository $peopleRepository)
    {
        $this->peopleRepository = $peopleRepository;
    }

    public function findRecipients(PreDeliveryInspection $preDeliveryInspection): array
    {
        $equipmentRecordAsm = $preDeliveryInspection->getEquipmentRecord()->getBuyer()?->getMainSalesRepresentative()?->asm;
        $recipients = [
            ...$this->peopleRepository->findGroupsMembers(['ROLE_PSM', 'ROLE_PSA'], $preDeliveryInspection->getEquipmentRecord()->getManufacturerLocation()),
            ...$this->peopleRepository->findGroupsMembers(['ROLE_SAM', 'ROLE_SA'], $preDeliveryInspection->getEquipmentRecord()->getSalesOrganisation()),
        ];

        if ($equipmentRecordAsm instanceof People) {
            $recipients[] = $equipmentRecordAsm;
        }

        return array_unique($recipients);
    }
}
