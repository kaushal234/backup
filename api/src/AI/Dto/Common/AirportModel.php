<?php

declare(strict_types=1);

namespace App\AI\Dto\Common;

final readonly class AirportModel
{
    public function __construct(
        public string $code,
        public string $type,
        public ?string $cityCode3,
        public string $cityName,
        public ?string $state,
        public ?string $country,
        public ?string $name,
        public string $source,
        public ?float $latitude,
        public ?float $longitude,
    ) {
    }
}
