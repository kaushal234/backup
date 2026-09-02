<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\FilterSpec;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;

/**
 * `path IS [NOT] NULL` toggle. True = IS NOT NULL, false = IS NULL.
 */
final readonly class ExistsSpec implements FilterSpec
{
    public function __construct(
        private string $name,
        private string $path,
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

        $dql = $paths->resolve($qb, $this->path);
        $qb->andWhere((bool) $value ? \sprintf('%s IS NOT NULL', $dql) : \sprintf('%s IS NULL', $dql));
    }
}
