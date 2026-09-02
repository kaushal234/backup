<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\FilterSpec;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;

/**
 * Boolean toggle on a to-many collection: `path IS [NOT] EMPTY`.
 * The $collection path must be the name of a collection-valued association on the root entity.
 */
final readonly class HasManySpec implements FilterSpec
{
    public function __construct(
        private string $name,
        private string $collection,
        private string $desc = '',
    ) {
    }

    public function toFields(): array
    {
        return [new FilterableField($this->name, FilterableField::TYPE_BOOL, description: $this->desc)];
    }

    public function applyTo(QueryBuilder $qb, PathResolver $paths, array $filters): void
    {
        $value = $filters[$this->name] ?? null;
        if (null === $value) {
            return;
        }

        $rootAlias = $qb->getRootAliases()[0];
        $expr = \sprintf('%s.%s', $rootAlias, $this->collection);
        $qb->andWhere((bool) $value ? \sprintf('%s IS NOT EMPTY', $expr) : \sprintf('%s IS EMPTY', $expr));
    }
}
