<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Handler;

use App\Doctrine\OwnerReflectionBag;
use App\Entity\News\News;

class NewsAuthorTransferHandler extends AbstractTransferHandler
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

        $qb
            ->andWhere($qb->expr()->gt('o.date', 'CURRENT_TIMESTAMP()'))
        ;

        $this->update($qb, $relation->getReflectionProperty()->getName(), $target);
    }

    public function getName(): string
    {
        return 'handler.news.author';
    }

    private function supports(string $className, \ReflectionProperty $property): bool
    {
        return News::class === $className && 'people' === $property->getName();
    }
}
