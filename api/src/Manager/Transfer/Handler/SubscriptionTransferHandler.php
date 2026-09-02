<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Handler;

use App\Doctrine\OwnerReflectionBag;
use App\Entity\Common\Subscription;

class SubscriptionTransferHandler extends AbstractTransferHandler
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

        $this->delete($qb);
    }

    public function getName(): string
    {
        return 'handler.follower';
    }

    private function supports(string $className, \ReflectionProperty $property): bool
    {
        return Subscription::class === $className && 'user' === $property->getName();
    }
}
