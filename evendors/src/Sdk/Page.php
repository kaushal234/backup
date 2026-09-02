<?php

declare(strict_types=1);

namespace App\Sdk;

use Psl\Collection\CollectionInterface;
use Psl\Math;

/**
 * @template T of Resource\ResourceInterface
 */
final class Page
{
    /**
     * @param CollectionInterface<T> $items
     */
    public function __construct(
        public readonly int $page,
        public readonly int $itemsPerPage,
        public readonly int $totalItems,
        public readonly bool $hasNext,
        public readonly bool $hasPrevious,
        public readonly CollectionInterface $items,
    ) {
    }

    public function getLastPage(): int
    {
        return (int) Math\ceil($this->totalItems / $this->itemsPerPage);
    }

    public function getPreviousPage(): ?int
    {
        if (!$this->hasPrevious) {
            return null;
        }

        $lastPage = $this->getLastPage();
        if ($this->page > $lastPage) {
            return $lastPage;
        }

        return $this->page - 1;
    }

    public function getNextPage(): ?int
    {
        if (!$this->hasNext) {
            return null;
        }

        return $this->page + 1;
    }
}
