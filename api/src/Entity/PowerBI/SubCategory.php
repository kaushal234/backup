<?php

declare(strict_types=1);

namespace App\Entity\PowerBI;

enum SubCategory: string
{
    case SSOControlling = 'SSO CONTROLLING';
    case FactoryControlling = 'FACTORY CONTROLLING';
    case Accounting = 'ACCOUNTING';
}
