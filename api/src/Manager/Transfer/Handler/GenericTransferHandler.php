<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Handler;

use App\Doctrine\OwnerReflectionBag;

class GenericTransferHandler extends AbstractTransferHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(?object $source, object $target, OwnerReflectionBag $relation, array $conditions = []): void
    {
        if (null === $source) {
            return;
        }

        $qb = $this->getQueryBuilder($source, $relation->getReflectionClass()->getName(), $relation->getReflectionProperty()->getName());

        foreach ($conditions as $key => $value) {
            switch (true) {
                case \is_bool($value):
                    //                case is_int($value):
                    $where = $qb->expr()->eq(\sprintf('o.%s', $key), ':'.$key);
                    break;
                case \is_string($value):
                    $where = $qb->expr()->like(\sprintf('o.%s', $key), ':'.$key);
                    break;
                default:
                    throw new \InvalidArgumentException(\sprintf('Only boolean, integers and strings are supported, got %s', \gettype($value)));
            }

            $qb
                ->andWhere($where)
                ->setParameter($key, $value);
        }

        $this->update($qb, $relation->getReflectionProperty()->getName(), $target);
    }

    public function getName(): string
    {
        return 'handler.generic';
    }
}
