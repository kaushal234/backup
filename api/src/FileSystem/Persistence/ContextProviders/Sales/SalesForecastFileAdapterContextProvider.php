<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Sales;

use App\Entity\Sales\SalesForecastFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;

class SalesForecastFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return SalesForecastFile::class;
    }

    public function getFileProperty(): string
    {
        return 'salesForecastFiles';
    }

    public function getDirectory(): string
    {
        return 'sales/sales_forecasts';
    }

    protected function getMimeTypes(): array
    {
        return [...parent::getMimeTypes(), ...['video/mp4']];
    }

    protected function getMaxSize(): ?string
    {
        return '20M';
    }
}
