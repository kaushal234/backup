<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Parts;

use App\Entity\Parts\SparePartsRequestFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;

class SparePartsRequestFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public function getFileProperty(): string
    {
        return 'sparePartsRequestFiles';
    }

    public static function getClass(): string
    {
        return SparePartsRequestFile::class;
    }

    public function getDirectory(): string
    {
        return 'parts/spare_parts_request';
    }
}
