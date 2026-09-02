<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Directory\BusinessUnit;
use App\Repository\Directory\PeopleRepository;

class BusinessUnitDeletionVoter implements DeletionVoterInterface
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
        return $entity instanceof BusinessUnit;
    }

    /**
     * {@inheritdoc}
     *
     * @param BusinessUnit $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('business unit')->setLabel($entity->getName());

        if ((bool) ($ids = $this->peopleRepository->getIdentifiersForBusinessUnit($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('PEOPLE')
            ;
        }

        return null;
    }
}
