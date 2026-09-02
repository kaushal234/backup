<?php

declare(strict_types=1);

namespace App\CQRS\Query\Airport;

use App\CQRS\Query\QueryInterface;

final class FindAllAirportsQuery implements QueryInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public readonly array $options,
    ) {
    }
}
