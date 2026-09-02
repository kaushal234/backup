<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Sales;

use App\Entity\Sales\CompetitorFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;

class CompetitorFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return CompetitorFile::class;
    }

    public function getFileProperty(): string
    {
        return 'competitorFiles';
    }

    public function getDirectory(): string
    {
        return 'sales/competitors';
    }
}
