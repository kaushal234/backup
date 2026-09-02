<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Quality\LocationArea;
use App\Repository\Quality\ToolRepository;

class LocationAreaDeletionVoter implements DeletionVoterInterface
{
    private readonly ToolRepository $toolRepository;

    public function __construct(ToolRepository $toolRepository)
    {
        $this->toolRepository = $toolRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof LocationArea;
    }

    /**
     * {@inheritdoc}
     *
     * @param LocationArea $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('location area')->setLabel((string) $entity->getName());

        if ((bool) ($ids = $this->toolRepository->getIdentifiersForLocationArea($entity))) {
            $type = \count($ids) > 1 ? 'Tools' : 'Tool';

            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType($type)
            ;
        }

        return null;
    }
}
