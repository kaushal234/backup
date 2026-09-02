<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Directory\Division;
use App\Entity\Directory\SubDivision;

class DivisionDeletionVoter implements DeletionVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof Division;
    }

    /**
     * {@inheritdoc}
     *
     * @param Division $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('division')->setLabel($entity->name);

        if (!$entity->getSubDivisions()->isEmpty()) {
            $ids = array_map(static fn (SubDivision $businessUnit) => ['id' => $businessUnit->getId()], $entity->getSubDivisions()->toArray());

            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('SubDivision')
            ;
        }

        return null;
    }
}
