<?php

declare(strict_types=1);

namespace App\CQRS\Query;

final class FindAllCountriesQuery implements QueryInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public readonly ?array $options = null,
    ) {
    }
}
