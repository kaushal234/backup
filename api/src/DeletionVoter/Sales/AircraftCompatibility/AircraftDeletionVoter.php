<?php

declare(strict_types=1);

namespace App\DeletionVoter\Sales\AircraftCompatibility;

use App\DeletionVoter\DeletionVoterInterface;
use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Sales\AircraftCompatibility\Aircraft;
use App\Repository\Sales\AircraftCompatibilityRepository;

class AircraftDeletionVoter implements DeletionVoterInterface
{
    public function __construct(
        private readonly AircraftCompatibilityRepository $repository,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof Aircraft;
    }

    /**
     * {@inheritdoc}
     *
     * @param Aircraft $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('Aircraft')->setLabel($entity->name);

        if ((bool) ($ids = $this->repository->getIdentifiersForAircraft($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('Aircraft Compatibility/ies')
            ;
        }

        return null;
    }
}
