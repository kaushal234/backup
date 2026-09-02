<?php

declare(strict_types=1);

namespace App\Snowflake\Filter;

use ApiPlatform\Metadata\FilterInterface as ApiPlatformFilterInterface;
use ApiPlatform\Metadata\Operation;
use App\Snowflake\QueryBuilder;

/**
 * Contract for filters applicable to a Snowflake-backed query builder.
 *
 * Mirrors ApiPlatform\Doctrine\Orm\Filter\FilterInterface, minus the
 * Doctrine-specific QueryNameGenerator dependency (not needed: our query
 * builder does not need generated alias names).
 */
interface FilterInterface extends ApiPlatformFilterInterface
{
    /**
     * @param class-string         $resourceClass
     * @param array<string, mixed> $context
     */
    public function apply(QueryBuilder $queryBuilder, string $resourceClass, ?Operation $operation = null, array $context = []): void;
}
