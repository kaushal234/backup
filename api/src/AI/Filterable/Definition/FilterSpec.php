<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition;

use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;

/**
 * One filter declared by an {@see AbstractFilterableDefinition}.
 *
 * A single spec may expose 1 or 2 fields to the LLM (e.g. a date range publishes
 * both `*After` and `*Before`). Each filter name listed in {@see toFields()} is
 * a key the LLM may set in the filters payload; the same spec receives all of
 * them via {@see applyTo()}.
 */
interface FilterSpec
{
    /**
     * @return FilterableField[]
     */
    public function toFields(): array;

    /**
     * @param array<string, mixed> $filters Raw filter payload (already validated for known keys)
     */
    public function applyTo(QueryBuilder $qb, PathResolver $paths, array $filters): void;
}
