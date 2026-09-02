<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Legal\Category;
use App\Repository\Legal\SubCategoryRepository;

class ContractCategoryDeletionVoter implements DeletionVoterInterface
{
    public function __construct(
        private readonly SubCategoryRepository $subCategoryRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof Category;
    }

    /**
     * {@inheritdoc}
     *
     * @param Category $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('category')->setLabel($entity->name);

        if ((bool) ($ids = $this->subCategoryRepository->getIdentifiersForCategory($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('SUB_CATEGORY');
        }

        return null;
    }
}
