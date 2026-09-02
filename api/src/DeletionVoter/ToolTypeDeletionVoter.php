<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Quality\CalibratedTools\ToolType;
use App\Repository\Quality\ToolRepository;

class ToolTypeDeletionVoter implements DeletionVoterInterface
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
        return $entity instanceof ToolType;
    }

    /**
     * {@inheritdoc}
     *
     * @param ToolType $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('Tool type')->setLabel($entity->getDescription());

        if ((bool) ($ids = $this->toolRepository->getIdentifiersForToolType($entity))) {
            $type = \count($ids) > 1 ? 'Tools' : 'Tool';

            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType($type)
            ;
        }

        return null;
    }
}
