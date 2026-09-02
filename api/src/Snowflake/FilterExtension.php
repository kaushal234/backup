<?php

declare(strict_types=1);

namespace App\Snowflake;

use ApiPlatform\Metadata\Operation;
use App\Snowflake\Filter\FilterInterface;
use App\Snowflake\Filter\OrderFilter;
use Psr\Container\ContainerInterface;

/**
 * Applies the "api_platform.filter"-tagged services declared on an operation
 * (via #[ApiFilter]) to a QueryBuilder.
 *
 * Mirrors ApiPlatform\Doctrine\Orm\Extension\FilterExtension: resolves each
 * filter id from $operation->getFilters() through the same
 * api_platform.filter_locator service Doctrine uses (already bound project-wide
 * as $filterLocator in config/services.yaml), and applies it if it's one of ours.
 */
class FilterExtension
{
    public function __construct(
        private readonly ContainerInterface $filterLocator,
    ) {
    }

    /**
     * @param class-string         $resourceClass
     * @param array<string, mixed> $context
     */
    public function applyToCollection(QueryBuilder $queryBuilder, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $resourceFilters = $operation?->getFilters();

        if (empty($resourceFilters)) {
            return;
        }

        $context['filters'] ??= [];

        /** @var list<OrderFilter> $orderFilters */
        $orderFilters = [];

        foreach ($resourceFilters as $filterId) {
            $filter = $this->filterLocator->has($filterId) ? $this->filterLocator->get($filterId) : null;

            if (!$filter instanceof FilterInterface) {
                continue;
            }

            // Apply ordering after every other filter, same convention as Doctrine's FilterExtension.
            if ($filter instanceof OrderFilter) {
                $orderFilters[] = $filter;
                continue;
            }

            $filter->apply($queryBuilder, $resourceClass, $operation, $context);
        }

        foreach ($orderFilters as $orderFilter) {
            $orderFilter->apply($queryBuilder, $resourceClass, $operation, $context);
        }
    }
}
