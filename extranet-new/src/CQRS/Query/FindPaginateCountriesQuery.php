<?php

declare(strict_types=1);

namespace App\CQRS\Query;

final class FindPaginateCountriesQuery implements QueryInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public ?int $page = null,
        public ?int $itemsPerPage = null,
        public array $options = [],
    ) {
    }
}
