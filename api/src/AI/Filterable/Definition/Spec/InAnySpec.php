<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\FilterSpec;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;

/**
 * `(path1 IN (:values) OR path2 IN (:values) …)` — one filter, OR'd across several columns
 * (e.g. failureCode1 OR failureCode2).
 */
final readonly class InAnySpec implements FilterSpec
{
    /**
     * @param string[] $paths
     */
    public function __construct(
        private string $name,
        private array $paths,
        private string $desc = '',
    ) {
    }

    public function toFields(): array
    {
        return [new FilterableField($this->name, FilterableField::TYPE_STRING, multi: true, description: $this->desc)];
    }

    public function applyTo(QueryBuilder $qb, PathResolver $paths, array $filters): void
    {
        $value = $filters[$this->name] ?? null;
        if (!\is_array($value) || [] === $value) {
            return;
        }

        $cast = array_map(static fn ($v): string => (string) $v, $value);
        $param = 'p_'.$this->name;

        $clauses = array_map(
            static fn (string $path): string => \sprintf('%s IN (:%s)', $paths->resolve($qb, $path), $param),
            $this->paths,
        );

        $qb->andWhere(implode(' OR ', $clauses))->setParameter($param, $cast);
    }
}
