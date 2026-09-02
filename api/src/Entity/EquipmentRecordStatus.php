<?php

declare(strict_types=1);

namespace App\Entity;

enum EquipmentRecordStatus: string
{
    case SHIPPED = 'SHIPPED';
    case GREEN_TAGGED = 'GREEN TAGGED';
    case INVOICED = 'INVOICED';
    case YELLOW_TAGGED = 'YELLOW TAGGED';
    case ACKNOWLEDGED = 'ACKNOWLEDGED';
    case IN__PRODUCTION = 'IN_PRODUCTION';
    case IN_PRODUCTION = 'IN PRODUCTION';
    case CONFIRMED = 'CONFIRMED';
    case PENDING = 'PENDING';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
