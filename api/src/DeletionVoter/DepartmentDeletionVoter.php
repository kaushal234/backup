<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Directory\Department;
use App\Repository\Directory\PeopleRepository;

class DepartmentDeletionVoter implements DeletionVoterInterface
{
    private readonly PeopleRepository $peopleRepository;

    public function __construct(PeopleRepository $peopleRepository)
    {
        $this->peopleRepository = $peopleRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof Department;
    }

    /**
     * {@inheritdoc}
     *
     * @param Department $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('department')->setLabel((string) $entity->getId());

        if ((bool) ($ids = $this->peopleRepository->getIdentifiersForDepartment($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('PEOPLE')
            ;
        }

        return null;
    }
}
