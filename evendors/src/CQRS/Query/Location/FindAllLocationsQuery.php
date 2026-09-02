<?php

declare(strict_types=1);

namespace App\CQRS\Query\Location;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\Location;

/**
 * @implements QueryInterface<array<string, list<Location>>>
 */
final class FindAllLocationsQuery implements QueryInterface
{
    /**
     * @param array<string, bool> $options
     */
    public function __construct(
        public readonly array $options = [],
    ) {
    }
}
