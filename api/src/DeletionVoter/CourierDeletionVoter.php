<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Parts\Courier;
use App\Repository\Parts\TrackingRepository;

class CourierDeletionVoter implements DeletionVoterInterface
{
    private readonly TrackingRepository $repository;

    public function __construct(TrackingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof Courier;
    }

    /**
     * {@inheritdoc}
     *
     * @param Courier $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('courier')->setLabel((string) $entity->getId());

        if ((bool) ($ids = $this->repository->getIdentifiersForCourier($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('Tracking')
            ;
        }

        return null;
    }
}
