<?php

declare(strict_types=1);

namespace App\CQRS\Query\Service;

use App\CQRS\Query\QueryInterface;

final class FindAllServiceBulletinsQuery implements QueryInterface
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
