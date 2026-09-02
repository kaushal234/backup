<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\FilterSpec;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;

/**
 * Escape hatch for shapes the DSL does not cover (e.g. boolean toggle over a list of "closed" statuses).
 * Use sparingly.
 */
final readonly class CustomSpec implements FilterSpec
{
    /**
     * @param FilterableField[]                                                $fields
     * @param callable(QueryBuilder, PathResolver, array<string, mixed>): void $apply
     */
    public function __construct(
        private array $fields,
        private mixed $apply,
    ) {
    }

    public function toFields(): array
    {
        return $this->fields;
    }

    public function applyTo(QueryBuilder $qb, PathResolver $paths, array $filters): void
    {
        ($this->apply)($qb, $paths, $filters);
    }
}
