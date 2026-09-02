<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Directory\People;

class PeopleDeletionVoter implements DeletionVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof People;
    }

    /**
     * {@inheritdoc}
     *
     * @param People $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        if ($entity->isEnabled()) {
            return (new RejectedDeletionReason())
                ->setType('people')
                ->setLabel($entity->getDisplayName())
            ;
        }

        return null;
    }
}
