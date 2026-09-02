<?php

declare(strict_types=1);

namespace App\Entity\Service;

enum TechnicianOnCallReplacement
{
    case SUPPLIER;
    case CUSTOMER;
    case QUOTATION;

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
