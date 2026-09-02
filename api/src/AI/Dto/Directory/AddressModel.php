<?php

declare(strict_types=1);

namespace App\AI\Dto\Directory;

final readonly class AddressModel
{
    public function __construct(
        public ?string $street1,
        public ?string $street2,
        public ?string $postalCode,
        public ?string $city,
        public ?string $town,
        public ?string $state,
        public ?string $country,
    ) {
    }
}
