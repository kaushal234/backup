<?php

declare(strict_types=1);

namespace App\Notifier\Quality\Crab;

use App\Entity\Quality\Crab;
use App\Entity\Quality\CrabCode;
use App\Entity\Quality\Derogation;
use App\Repository\Directory\PeopleRepository;
use LegacyBundle\Manager\EquipmentRecordManager;

class RecipientsFinder
{
    public function __construct(
        private readonly PeopleRepository $peopleRepository,
        private readonly EquipmentRecordManager $equipmentRecordManager,
    ) {
    }

    public function findTos(Crab $crab): array
    {
        $roles = ['ROLE_GL', 'ROLE_PS', 'ROLE_QA', 'ROLE_QAM', 'ROLE_PM', 'role_MPE'];

        if (CrabCode::FAI === $crab->code->description) {
            $roles[] = 'ROLE_BYR';
        }

        return [
            ...$this->peopleRepository->findGroupsMembers($roles, $crab->equipmentRecord->getManufacturerLocation()),
        ];
    }

    public function findYellowTagTos(Crab $crab): array
    {
        $recipients = $this->equipmentRecordManager->isSolLinked($crab->equipmentRecord->getLegacyId()) ? [$this->peopleRepository->findGroupsMembers(['ROLE_SA', 'ROLE_TM'], $crab->equipmentRecord->getSalesOrganisation())] : [];

        $roles = ['ROLE_QE', 'ROLE_PM', 'ROLE_QAM', 'ROLE_PSM', 'ROLE_PSA', 'ROLE_PSE', 'ROLE_EM', 'ROLE_COO', 'ROLE_FC', 'ROLE_MLM', 'ROLE_PLANNER', 'role_MPE', 'ROLE_QA'];

        if (CrabCode::FAI === $crab->code->description) {
            $roles[] = 'ROLE_BYR';
        }

        return [
            ...$recipients,
            ...$this->peopleRepository->findGroupsMembers($roles, $crab->equipmentRecord->getManufacturerLocation()),
        ];
    }

    public function findDerogationCcs(Derogation $derogation): array
    {
        $crab = $derogation->getCrabs()->first();

        return [
            ...$this->peopleRepository->findGroupsMembers(['ROLE_QAM', 'role_MPE'], $crab->equipmentRecord->getManufacturerLocation()),
        ];
    }

    public function findDerogationClosedTos(Derogation $derogation): array
    {
        /** @var Crab $crab */
        $crab = $derogation->getCrabs()->first();

        $recipients = [];
        if (null !== ($location = $crab->equipmentRecord->getManufacturerLocation())) {
            $recipients = [
                ...$this->peopleRepository->findGroupsMembers(['ROLE_PM', 'ROLE_GL', 'ROLE_PS', 'ROLE_PM', 'ROLE_QAM', 'ROLE_QA'], $crab->equipmentRecord->getManufacturerLocation()),
            ];

            if (null !== $recipient = $this->peopleRepository->findOneByPositionByLocation($crab->department->derogationPosition, $location)) {
                $recipients = [
                    ...$recipients,
                    $recipient,
                ];
            }
        }

        return [
            ...$recipients,
            $crab->createdBy,
        ];
    }

    public function findDerogationExpiredTos(Derogation $derogation): array
    {
        $crab = $derogation->getCrabs()->first();

        $recipients = [
            $derogation->assignee,
            $derogation->assignee->getSupervisor(),
        ];

        if (null !== $crab->equipmentRecord) {
            $recipients = [
                ...$recipients,
                ...$this->peopleRepository->findGroupMembers('ROLE_PM', $crab->equipmentRecord->getManufacturerLocation()),
                ...$this->peopleRepository->findGroupMembers('GG_QUALITY', $crab->equipmentRecord->getManufacturerLocation()),
                ...$this->peopleRepository->findGroupMembers('ROLE_QAM', $crab->equipmentRecord->getManufacturerLocation()),
            ];
        }

        return $recipients;
    }
}
