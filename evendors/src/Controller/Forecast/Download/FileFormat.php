<?php

declare(strict_types=1);

namespace App\Controller\Forecast\Download;

enum FileFormat: string
{
    case Csv = 'csv';
    case Xlsx = 'xlsx';
}
