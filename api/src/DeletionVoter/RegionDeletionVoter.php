<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Region;

class RegionDeletionVoter implements DeletionVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof Region;
    }

    /**
     * {@inheritdoc}
     *
     * @param Region $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('region')->setLabel((string) $entity->getName());

        if (!$entity->getBusinessUnits()->isEmpty()) {
            $ids = array_map(static fn (BusinessUnit $businessUnit) => ['id' => $businessUnit->getId()], $entity->getBusinessUnits()->toArray());

            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('BU')
            ;
        }

        return null;
    }
}
