<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Directory\Position;
use App\Repository\Directory\PeopleRepository;

class PositionDeletionVoter implements DeletionVoterInterface
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
        return $entity instanceof Position;
    }

    /**
     * {@inheritdoc}
     *
     * @param Position $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('position')->setLabel((string) $entity->getDescription());

        if ((bool) ($ids = $this->peopleRepository->getIdentifiersForPosition($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('people')
            ;
        }

        return null;
    }
}
