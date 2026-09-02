<?php

declare(strict_types=1);

namespace App\Entity;

enum IndiceFactor: string
{
    case IF_1 = 'IF 1';
    case IF_10 = 'IF 10';
    case IF_100 = 'IF 100';
    case IF_1000 = 'IF 1000';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
