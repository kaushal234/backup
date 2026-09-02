<?php

declare(strict_types=1);

namespace App\AI\Dto\Sales\EquipmentShippingRecord;

final readonly class IncotermModel
{
    public function __construct(
        public string $code,
    ) {
    }
}
