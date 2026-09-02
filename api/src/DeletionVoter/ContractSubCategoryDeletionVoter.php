<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Legal\SubCategory;
use App\Repository\Legal\ContractRepository;

class ContractSubCategoryDeletionVoter implements DeletionVoterInterface
{
    public function __construct(
        private readonly ContractRepository $contractRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof SubCategory;
    }

    /**
     * {@inheritdoc}
     *
     * @param SubCategory $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('sub category')->setLabel($entity->name);

        if ((bool) ($ids = $this->contractRepository->getIdentifiersForSubCategory($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('CONTRACT');
        }

        return null;
    }
}
