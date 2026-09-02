<?php

declare(strict_types=1);

namespace App\CQRS\Query\EquipmentSerial;

use App\CQRS\Query\QueryInterface;

final class FindAllEquipmentSerialsQuery implements QueryInterface
{
    public function __construct(
        public string $equipmentRecordSerialNumber,
        public bool $schematics = true,
    ) {
    }
}
