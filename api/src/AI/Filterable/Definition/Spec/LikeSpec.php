<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\FilterSpec;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;

/**
 * `path LIKE %:value%`.
 */
final readonly class LikeSpec implements FilterSpec
{
    public function __construct(
        private string $name,
        private string $path,
        private string $desc = '',
    ) {
    }

    public function toFields(): array
    {
        return [new FilterableField($this->name, FilterableField::TYPE_STRING, description: $this->desc)];
    }

    public function applyTo(QueryBuilder $qb, PathResolver $paths, array $filters): void
    {
        $value = $filters[$this->name] ?? null;
        if (null === $value || '' === $value) {
            return;
        }

        $param = 'p_'.$this->name;
        $qb->andWhere(\sprintf('%s LIKE :%s', $paths->resolve($qb, $this->path), $param))
            ->setParameter($param, \sprintf('%%%s%%', (string) $value));
    }
}
