<?php

declare(strict_types=1);

namespace App\AI\Dto\Directory;

final readonly class PremiseModel
{
    public function __construct(
        public string $name,
        public string $description,
        public ?float $latitude,
        public ?float $longitude,
        public AddressModel $address,
    ) {
    }
}
