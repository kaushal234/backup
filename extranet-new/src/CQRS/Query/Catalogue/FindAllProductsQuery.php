<?php

declare(strict_types=1);

namespace App\CQRS\Query\Catalogue;

use App\CQRS\Query\QueryInterface;

final class FindAllProductsQuery implements QueryInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public readonly array $options = [],
    ) {
    }
}
