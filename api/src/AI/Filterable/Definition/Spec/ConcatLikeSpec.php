<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\FilterSpec;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;

/**
 * `CONCAT(path1, ' ', path2, …) LIKE %:value%` — typical for free-text search on a person's full name.
 */
final readonly class ConcatLikeSpec implements FilterSpec
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
        return [new FilterableField($this->name, FilterableField::TYPE_STRING, description: $this->desc)];
    }

    public function applyTo(QueryBuilder $qb, PathResolver $paths, array $filters): void
    {
        $value = $filters[$this->name] ?? null;
        if (null === $value || '' === $value) {
            return;
        }

        $resolved = array_map(static fn (string $p): string => $paths->resolve($qb, $p), $this->paths);
        $args = implode(", ' ', ", $resolved);
        $param = 'p_'.$this->name;

        $qb->andWhere(\sprintf('CONCAT(%s) LIKE :%s', $args, $param))
            ->setParameter($param, \sprintf('%%%s%%', (string) $value));
    }
}
