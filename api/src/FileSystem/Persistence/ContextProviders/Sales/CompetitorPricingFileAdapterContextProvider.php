<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Sales;

use App\Entity\Sales\CompetitorPricingFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;

class CompetitorPricingFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return CompetitorPricingFile::class;
    }

    public function getFileProperty(): string
    {
        return 'competitorPricingFiles';
    }

    public function getDirectory(): string
    {
        return 'sales/competitor_pricings';
    }
}
