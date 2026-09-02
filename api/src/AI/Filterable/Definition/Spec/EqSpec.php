<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\FilterSpec;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;

/**
 * `path = :value` (scalar equality).
 */
final readonly class EqSpec implements FilterSpec
{
    public function __construct(
        private string $name,
        private string $path,
        private string $type,
        private string $desc = '',
    ) {
    }

    public function toFields(): array
    {
        return [new FilterableField($this->name, $this->type, description: $this->desc)];
    }

    public function applyTo(QueryBuilder $qb, PathResolver $paths, array $filters): void
    {
        $value = $filters[$this->name] ?? null;
        if (null === $value || '' === $value) {
            return;
        }

        $cast = FilterableField::TYPE_INT === $this->type ? (int) $value : (string) $value;
        $param = 'p_'.$this->name;

        $qb->andWhere(\sprintf('%s = :%s', $paths->resolve($qb, $this->path), $param))
            ->setParameter($param, $cast);
    }
}
