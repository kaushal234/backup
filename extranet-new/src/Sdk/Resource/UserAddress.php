<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

final class UserAddress
{
    public function __construct(
        public readonly ?string $street = null,
        public readonly ?string $street2 = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null,
        public readonly ?string $postalCode = null,
        public readonly ?Country $country = null,
    ) {
    }
}
