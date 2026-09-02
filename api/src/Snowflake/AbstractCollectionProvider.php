<?php

declare(strict_types=1);

namespace App\Snowflake;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\Http\SnowflakeClient;

/**
 * Base class for Snowflake-backed ApiPlatform collection providers.
 *
 * Handles everything generic: SELECT list from #[Column], applying
 * filters (FilterExtension), pagination (ApiPlatform's own
 * Pagination service, count query, paginator). A concrete provider only
 * needs to implement:
 *
 *  - getResourceClass(): which DTO this provider is for
 *  - configureQuery(): the FROM/JOIN for this resource (kept in the
 *    provider file on purpose, not in an attribute)
 *  - mapRow(): how a Snowflake row becomes that DTO
 *
 * @template T of object
 */
abstract class AbstractCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly SnowflakeClient $snowflakeClient,
        private readonly FilterExtension $filterExtension,
        private readonly Pagination $pagination,
    ) {
    }

    /**
     * @return CollectionPaginator<T>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CollectionPaginator
    {
        $resourceClass = $this->getResourceClass();
        $columns = ColumnMapper::getColumns($resourceClass);

        $queryBuilder = new QueryBuilder($this->snowflakeClient);
        $queryBuilder->select(...array_values($columns));
        $this->configureQuery($queryBuilder);

        $this->filterExtension->applyToCollection($queryBuilder, $resourceClass, $operation, $context);

        $paginationEnabled = $this->pagination->isEnabled($operation, $context);
        $page = 1.0;
        $limit = 0.0;

        if ($paginationEnabled) {
            [$page, $offset, $limit] = $this->pagination->getPagination($operation, $context);
            $queryBuilder->setFirstResult($offset)->setMaxResults($limit);
        }

        $rows = $queryBuilder->getResult();
        $totalItems = $paginationEnabled ? $queryBuilder->getCount() : \count($rows);

        if (!$paginationEnabled) {
            $limit = $totalItems;
        }

        return new CollectionPaginator(
            items: array_map($this->mapRow(...), $rows),
            currentPage: (float) $page,
            itemsPerPage: (float) $limit,
            totalItems: (float) $totalItems,
        );
    }

    /**
     * @return class-string<T>
     */
    abstract protected function getResourceClass(): string;

    /**
     * Adds FROM/JOIN (and anything else specific to this resource's base query).
     * Columns and WHERE/ORDER BY/LIMIT are handled by the base class.
     */
    abstract protected function configureQuery(QueryBuilder $queryBuilder): void;

    /**
     * @param array<string, mixed> $row
     *
     * @return T
     */
    abstract protected function mapRow(array $row): object;

    /**
     * Reads $row at the column declared by #[Column] for $property,
     * e.g. "c.SITE" -> looks up $row['SITE']. Helper for mapRow() implementations.
     *
     * @param array<string, mixed> $row
     */
    protected function columnValue(array $row, string $property): mixed
    {
        $column = ColumnMapper::getColumn($this->getResourceClass(), $property);
        $key = mb_strtoupper(mb_substr((string) mb_strrchr($column, '.'), 1));

        return $row[$key] ?? null;
    }
}
