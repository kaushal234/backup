<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Handler;

use App\Doctrine\OwnerReflectionBag;
use App\Entity\Sales\Demo;

class DemoRepresentativeTransferHandler extends AbstractTransferHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(?object $source, object $target, OwnerReflectionBag $relation, array $conditions = []): void
    {
        if (null === $source) {
            return;
        }

        if (!$this->supports($relation->getReflectionClass()->getName(), $relation->getReflectionProperty())) {
            return;
        }

        $qb = $this->getQueryBuilder($source, $relation->getReflectionClass()->getName(), $relation->getReflectionProperty()->getName());

        $qb->andWhere($qb->expr()->in('o.status', Demo::OPEN_STATUSES));
        $this->update($qb, $relation->getReflectionProperty()->getName(), $target);
    }

    public function getName(): string
    {
        return 'handler.demo.representatives';
    }

    private function supports(string $className, \ReflectionProperty $property): bool
    {
        return Demo::class === $className && \in_array($property->getName(), ['ast', 'asm', 'psm'], true);
    }
}
