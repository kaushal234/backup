<?php

declare(strict_types=1);

namespace App\Snowflake\Filter;

use ApiPlatform\Metadata\Operation;
use App\Snowflake\ColumnMapper;
use App\Snowflake\QueryBuilder;

/**
 * Exact or partial match filter for Snowflake-backed resources.
 *
 * The SQL column for each property comes from that property's
 * #[Column] attribute (via ColumnMapper) -- not repeated here.
 *
 *   #[ApiFilter(SearchFilter::class, properties: ['site', 'item', 'description' => 'partial'])]
 */
class SearchFilter implements FilterInterface
{
    private const STRATEGY_EXACT = 'exact';
    private const STRATEGY_PARTIAL = 'partial';

    /**
     * @param array<int|string, string> $properties list of property names (exact match), or property => 'exact'|'partial'
     */
    public function __construct(
        private readonly array $properties = [],
    ) {
    }

    public function apply(QueryBuilder $queryBuilder, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $filters = $context['filters'] ?? [];

        foreach ($this->normalizeProperties() as $property => $strategy) {
            if (!isset($filters[$property]) || '' === $filters[$property]) {
                continue;
            }

            $column = ColumnMapper::getColumn($resourceClass, $property);
            $value = (string) $filters[$property];

            if (self::STRATEGY_PARTIAL === $strategy) {
                $queryBuilder->andWhere(\sprintf('%s ILIKE ?', $column), ['%'.$value.'%']);
            } else {
                $queryBuilder->andWhere(\sprintf('%s = ?', $column), [$value]);
            }
        }
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];

        foreach ($this->normalizeProperties() as $property => $strategy) {
            $description[$property] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'strategy' => $strategy,
            ];
        }

        return $description;
    }

    /**
     * @return array<string, string> property => strategy
     */
    private function normalizeProperties(): array
    {
        $normalized = [];

        foreach ($this->properties as $key => $value) {
            if (\is_int($key)) {
                $normalized[$value] = self::STRATEGY_EXACT;
            } else {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }
}
