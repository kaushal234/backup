<?php

declare(strict_types=1);

namespace App\AI\Dto\Sales\EquipmentShippingRecord;

use App\AI\Dto\Support\EquipmentRecordModel;

final readonly class EquipmentShippingRecordLineModel
{
    public function __construct(
        public ?EquipmentRecordModel $equipmentRecord,
        public ?\DateTimeInterface $pickupDate,
        public ?\DateTimeInterface $deliveryDate,
    ) {
    }
}
