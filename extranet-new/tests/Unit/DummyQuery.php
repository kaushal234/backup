<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\CQRS\Query\QueryInterface;

class DummyQuery implements QueryInterface
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
