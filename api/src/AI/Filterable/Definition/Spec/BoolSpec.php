<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\FilterSpec;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;

/**
 * `path = :trueValue` / `:falseValue` — supports columns that store Y/N flags instead of booleans
 * (see MasterEngineeringActivityProcess::isPrivate which uses 'Y'/'N').
 */
final readonly class BoolSpec implements FilterSpec
{
    public function __construct(
        private string $name,
        private string $path,
        private mixed $trueValue,
        private mixed $falseValue,
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

        $bound = (bool) $value ? $this->trueValue : $this->falseValue;
        $param = 'p_'.$this->name;

        $qb->andWhere(\sprintf('%s = :%s', $paths->resolve($qb, $this->path), $param))
            ->setParameter($param, $bound);
    }
}
