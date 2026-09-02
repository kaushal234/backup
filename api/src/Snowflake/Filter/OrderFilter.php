<?php

declare(strict_types=1);

namespace App\Snowflake\Filter;

use ApiPlatform\Metadata\Operation;
use App\Snowflake\ColumnMapper;
use App\Snowflake\QueryBuilder;

/**
 * Sorting filter for Snowflake-backed resources, read from "?order[property]=asc|desc",
 * same query parameter convention as Doctrine's OrderFilter.
 *
 * The SQL column comes from the property's #[Column] attribute
 * (via ColumnMapper) -- not repeated here.
 *
 *   #[ApiFilter(OrderFilter::class, properties: ['site', 'item', 'price'])]
 *
 * Like Doctrine's OrderFilter, ApiPlatform's AttributeFilterPass always normalizes
 * the "properties" attribute argument into an associative array keyed by property
 * name (e.g. ['site' => null, 'item' => null, 'price' => null]) before injecting it
 * here, regardless of whether the attribute was written as a plain list or not.
 */
class OrderFilter implements FilterInterface
{
    /**
     * @param array<string, mixed> $properties orderable property names, as keys (values are unused)
     */
    public function __construct(
        private readonly array $properties = [],
        private readonly string $orderParameterName = 'order',
    ) {
    }

    public function apply(QueryBuilder $queryBuilder, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $orders = $context['filters'][$this->orderParameterName] ?? [];

        if (!\is_array($orders)) {
            return;
        }

        foreach ($orders as $property => $direction) {
            if (!\array_key_exists($property, $this->properties)) {
                continue;
            }

            $direction = mb_strtoupper((string) $direction);
            if (!\in_array($direction, ['ASC', 'DESC'], true)) {
                continue;
            }

            $queryBuilder->orderBy(ColumnMapper::getColumn($resourceClass, $property), $direction);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];

        foreach (array_keys($this->properties) as $property) {
            $description[\sprintf('%s[%s]', $this->orderParameterName, $property)] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']],
            ];
        }

        return $description;
    }
}
