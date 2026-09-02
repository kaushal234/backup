<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Directory\PositionCategory;
use App\Entity\Directory\PositionCategoryType;
use Doctrine\ORM\EntityManagerInterface;

class PositionCategoryTypeDeletionVoter implements DeletionVoterInterface
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof PositionCategoryType;
    }

    /**
     * {@inheritdoc}
     *
     * @param PositionCategoryType $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('position category type')->setLabel($entity->name);

        if ((bool) ($categories = $this->entityManager->getRepository(PositionCategory::class)->findBy(['positionCategoryType' => $entity]))) {
            return $reason
                ->setIdentifiers(array_map(static fn (PositionCategory $positionCategory) => $positionCategory->name, $categories))
                ->setCountedType('position category(ies)')
            ;
        }

        return null;
    }
}
