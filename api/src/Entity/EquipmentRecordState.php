<?php

declare(strict_types=1);

namespace App\Entity;

enum EquipmentRecordState
{
    case ACTIVE;
    case RETIRED;

    public static function name(): array
    {
        return array_column(self::cases(), 'name');
    }
}
