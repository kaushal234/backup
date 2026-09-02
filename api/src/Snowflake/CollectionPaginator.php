<?php

declare(strict_types=1);

namespace App\Snowflake;

use ApiPlatform\State\Pagination\PaginatorInterface;

/**
 * @template T of object
 *
 * @implements PaginatorInterface<T>
 * @implements \IteratorAggregate<T>
 */
class CollectionPaginator implements PaginatorInterface, \IteratorAggregate
{
    /**
     * @param list<T> $items items of the current page only
     */
    public function __construct(
        private readonly array $items,
        private readonly float $currentPage,
        private readonly float $itemsPerPage,
        private readonly float $totalItems,
    ) {
    }

    public function getCurrentPage(): float
    {
        return $this->currentPage;
    }

    public function getItemsPerPage(): float
    {
        return $this->itemsPerPage;
    }

    public function getLastPage(): float
    {
        if (0.0 === $this->itemsPerPage) {
            return 1.0;
        }

        return max(1.0, ceil($this->totalItems / $this->itemsPerPage));
    }

    public function getTotalItems(): float
    {
        return $this->totalItems;
    }

    public function count(): int
    {
        return \count($this->items);
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->items);
    }
}
