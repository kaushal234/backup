<?php

declare(strict_types=1);

namespace App\CQRS\Query\Catalogue;

use App\CQRS\Query\QueryInterface;

final class FindProductTypeQuery implements QueryInterface
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}
