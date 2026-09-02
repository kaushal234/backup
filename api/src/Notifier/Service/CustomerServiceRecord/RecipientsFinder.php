<?php

declare(strict_types=1);

namespace App\Notifier\Service\CustomerServiceRecord;

use App\Entity\Directory\Region;
use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Repository\Directory\PeopleRepository;

class RecipientsFinder
{
    public function __construct(
        private readonly PeopleRepository $peopleRepository,
    ) {
    }

    public function findTos(CommissioningCustomerServiceRecord $commissioningCustomerServiceRecord): array
    {
        $recipients = [
            ...$this->peopleRepository->findGroupsMembers(['ROLE_COO', 'ROLE_PM', 'ROLE_QAM', 'ROLE_QA', 'ROLE_RME', 'ROLE_EM', 'ROLE_PSM', 'ROLE_PSE', 'ROLE_PSA', 'ROLE_GL', 'ROLE_PS'], $commissioningCustomerServiceRecord->equipmentRecord->getManufacturerLocation()),
            ...$this->peopleRepository->findGroupsMembersByRegion(['ROLE_RCEO', 'role_RCOO'], $commissioningCustomerServiceRecord->equipmentRecord->getManufacturerLocation()->getBusinessUnit()->getRegion()),
            ...$this->peopleRepository->findGroupsMembersByRegion(['ROLE_CEO', 'ROLE_COO'], Region::GSE),
            ...$this->peopleRepository->findGroupsMembersByRegion(['ROLE_COO', 'ROLE_CSD', 'ROLE_CMO'], Region::ALVEST),
        ];

        foreach ($commissioningCustomerServiceRecord->getInterventions() as $intervention) {
            $recipients[] = $intervention->leader;
        }

        return array_values(array_unique(array_filter($recipients)));
    }

    public function findCcs(CommissioningCustomerServiceRecord $commissioningCustomerServiceRecord): array
    {
        $author = $commissioningCustomerServiceRecord->createdBy;
        $supervisor = $author ? $author->getSupervisor() : null;

        return array_filter([$author, $supervisor], static fn ($person) => null !== $person);
    }
}
