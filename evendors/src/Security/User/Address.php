<?php

declare(strict_types=1);

namespace App\Security\User;

final class Address
{
    public function __construct(
        public readonly string $firstLine,
        public readonly string $secondLine,
        public readonly string $city,
        public readonly string $state,
        public readonly string $country,
        public readonly string $zipCode,
    ) {
    }
}
