<?php

declare(strict_types=1);

namespace App\AI\Dto\Sales\EquipmentShippingRecord;

final readonly class EquipmentShippingRecordCostModel
{
    public function __construct(
        public ?float $amount,
        public ?string $currency,
        public ?string $description,
    ) {
    }
}
