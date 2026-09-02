<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\FilterSpec;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;

/**
 * `path IN (:values)`. If $path is a bare to-one relation on the root entity
 * (no dot), uses the `IDENTITY(rootAlias.relation) IN (:values)` shortcut to
 * avoid a join — matches the legacy `IDENTITY(...) IN` pattern.
 */
final readonly class InSpec implements FilterSpec
{
    /**
     * Cap on the number of values accepted per `IN (...)` filter. A misbehaving LLM that
     * blows past this would otherwise let us ship arbitrarily large `IN` lists straight
     * to the database.
     */
    public const int MAX_VALUES = 100;

    /**
     * @param string[]|null $enum
     */
    public function __construct(
        private string $name,
        private string $path,
        private string $type,
        private ?array $enum = null,
        private string $desc = '',
    ) {
    }

    public function toFields(): array
    {
        return [new FilterableField($this->name, $this->type, multi: true, enum: $this->enum, description: $this->desc)];
    }

    public function applyTo(QueryBuilder $qb, PathResolver $paths, array $filters): void
    {
        $value = $filters[$this->name] ?? null;
        if (null === $value || [] === $value) {
            return;
        }

        if (!\is_array($value)) {
            throw new \InvalidArgumentException(\sprintf('Filter "%s" expects an array of values, got %s. Wrap a single value in an array.', $this->name, get_debug_type($value)));
        }

        if (\count($value) > self::MAX_VALUES) {
            throw new \InvalidArgumentException(\sprintf('Filter "%s" accepts at most %d values, got %d. Narrow the selection.', $this->name, self::MAX_VALUES, \count($value)));
        }

        $cast = FilterableField::TYPE_INT === $this->type
            ? array_map(static fn ($v): int => (int) $v, $value)
            : array_map(static fn ($v): string => (string) $v, $value);

        $param = 'p_'.$this->name;
        $dql = $paths->resolveIdentity($this->path) ?? $paths->resolve($qb, $this->path);

        $qb->andWhere(\sprintf('%s IN (:%s)', $dql, $param))
            ->setParameter($param, $cast);
    }
}
