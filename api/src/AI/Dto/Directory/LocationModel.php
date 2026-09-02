<?php

declare(strict_types=1);

namespace App\AI\Dto\Directory;

final readonly class LocationModel
{
    public function __construct(
        public string $name,
        public ?int $erp,
    ) {
    }
}
