<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\News\NewsCategory;
use App\Repository\News\NewsRepository;

class NewsCategoryDeletionVoter implements DeletionVoterInterface
{
    private readonly NewsRepository $newsRepository;

    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof NewsCategory;
    }

    /**
     * {@inheritdoc}
     *
     * @param NewsCategory $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('news category')->setLabel((string) $entity->getName());

        if ((bool) ($ids = $this->newsRepository->getIdentifiersForNewsCategory($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('News')
            ;
        }

        return null;
    }
}
