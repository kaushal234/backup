<?php

declare(strict_types=1);

namespace App\Doctrine\EventListener\Sales;

use App\Entity\Sales\Order;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use LegacyBundle\Manager\SalesOrderLineManager;

#[AsDoctrineListener(Events::preRemove)]
class OrderDeletionListener
{
    private readonly SalesOrderLineManager $salesOrderLineManager;

    public function __construct(SalesOrderLineManager $salesOrderLineManager)
    {
        $this->salesOrderLineManager = $salesOrderLineManager;
    }

    public function preRemove(PreRemoveEventArgs $args)
    {
        $order = $args->getObject();

        if (!$order instanceof Order) {
            return;
        }

        $this->salesOrderLineManager->deleteSalesOrderLines($order);
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::preRemove,
        ];
    }
}
