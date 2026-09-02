<?php

declare(strict_types=1);

namespace App\Agile;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Division;
use App\Entity\Directory\Position;

class SynchronizationFilters
{
    public const EXCLUDE_DIVISIONS_ID = [
        Division::INTEGRATED_THIRD_PARTIES => 6,
    ];

    public const EXCLUDE_BUSINESS_UNITS_ID = [
        BusinessUnit::ALVEST_ARABIA_EQUIPMENT_SERVICES => 78,
    ];

    public const EXCLUDE_PEOPLE_ID = [
        'mis.emeai.pa@tld-europe.com' => 80,
        'webmaster@tld-gse.com' => 1174,
        'yannick.leveque@tld-europe.com' => 15887,
        'admin.kernl@tld-europe.com' => 37845,
    ];

    public const EXCLUDE_POSITIONS_ID = [
        Position::SHOP_FLOOR_EMPLOYEE => 49,
    ];
}
