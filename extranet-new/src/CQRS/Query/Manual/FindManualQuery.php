<?php

declare(strict_types=1);

namespace App\CQRS\Query\Manual;

use App\CQRS\Query\QueryInterface;

final readonly class FindManualQuery implements QueryInterface
{
    public function __construct(
        public int $id,
    ) {
    }
}
