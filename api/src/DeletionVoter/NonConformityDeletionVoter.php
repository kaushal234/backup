<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Quality\NonConformity;
use App\Repository\Purchasing\NCRVendorWarrantyClaimRepository;
use App\Repository\Quality\Crab\CrabRepository;

class NonConformityDeletionVoter implements DeletionVoterInterface
{
    public function __construct(
        private readonly NCRVendorWarrantyClaimRepository $ncrVendorWarrantyClaimRepository,
        private readonly CrabRepository $crabRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof NonConformity;
    }

    /**
     * {@inheritdoc}
     *
     * @param NonConformity $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('Non Conformity')->setLabel((string) $entity->getId());

        if ((bool) ($ids = $this->ncrVendorWarrantyClaimRepository->getIdentifiersForNonConformity($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('Vendor Warranty Claim')
            ;
        }

        if ((bool) ($ids = $this->crabRepository->getIdentifiersForNonConformity($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('Crab')
            ;
        }

        return null;
    }
}
