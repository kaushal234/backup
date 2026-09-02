<?php

declare(strict_types=1);

namespace App\AI\Dto\Support;

final readonly class EquipmentSerialModel
{
    public function __construct(
        public ?string $model,
        public ?string $serial,
        public ?string $brand,
    ) {
    }
}
