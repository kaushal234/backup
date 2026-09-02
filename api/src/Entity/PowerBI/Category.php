<?php

declare(strict_types=1);

namespace App\Entity\PowerBI;

enum Category: string
{
    case Engineering = 'ENGINEERING';
    case Finance = 'FINANCE';
    case Manufacturing = 'MANUFACTURING';
    case Materials = 'MATERIALS';
    case Warehouse = 'WAREHOUSE';
}
