<?php

declare(strict_types=1);

namespace App\ION\Enum;

enum SupplySource: int
{
    case NotApplicable = 10;
    case JobShop = 20;
    case Repetitive = 25;
    case Assembly = 30;
    case Purchase = 40;
    case Subcontract = 50;
    case Distribution = 60;
}
