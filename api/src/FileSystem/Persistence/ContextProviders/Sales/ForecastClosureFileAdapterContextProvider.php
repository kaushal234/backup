<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Sales;

use App\Entity\Sales\ForecastClosureFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;

class ForecastClosureFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return ForecastClosureFile::class;
    }

    public function getFileProperty(): string
    {
        return 'forecastClosureFiles';
    }

    public function getDirectory(): string
    {
        return 'sales/forecast_closures';
    }
}
