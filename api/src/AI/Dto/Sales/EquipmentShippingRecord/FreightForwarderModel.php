<?php

declare(strict_types=1);

namespace App\AI\Dto\Sales\EquipmentShippingRecord;

final readonly class FreightForwarderModel
{
    public function __construct(
        public string $name,
    ) {
    }
}
