<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Sales\Order;
use LegacyBundle\Manager\SalesOrderLineManager;

class OrderDeletionVoter implements DeletionVoterInterface
{
    private readonly SalesOrderLineManager $salesOrderLineManager;

    public function __construct(SalesOrderLineManager $salesOrderLineManager)
    {
        $this->salesOrderLineManager = $salesOrderLineManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof Order;
    }

    /**
     * {@inheritdoc}
     *
     * @param Order $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('Sales Order Line')->setLabel((string) $entity->getLegacyId());

        if ([] !== ($ids = $this->salesOrderLineManager->getSalesOrderLinesIDs($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('SOL')
            ;
        }

        return null;
    }
}
