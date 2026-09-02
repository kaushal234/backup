<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Handler;

use App\Doctrine\OwnerReflectionBag;
use App\Entity\Sales\Order;

class OrderTransferHandler extends AbstractTransferHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle($source, $target, OwnerReflectionBag $relation, array $conditions = []): void
    {
        if (!$this->supports($relation->getReflectionClass()->getName(), $relation->getReflectionProperty())) {
            return;
        }

        $qb = $this->getQueryBuilder($source, $relation->getReflectionClass()->getName(), $relation->getReflectionProperty()->getName());

        $qb
            ->andWhere('o.status != :status')
            ->setParameter('status', Order::CLOSED)
        ;

        $this->update($qb, $relation->getReflectionProperty()->getName(), $target);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'handler.sales_orders.asm';
    }

    /**
     * {@inheritdoc}
     */
    private function supports(string $className, \ReflectionProperty $property): bool
    {
        return Order::class === $className && 'asm' === $property->getName();
    }
}
