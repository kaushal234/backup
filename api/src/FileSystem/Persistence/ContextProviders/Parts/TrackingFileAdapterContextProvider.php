<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Parts;

use App\Entity\Parts\TrackingFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;

class TrackingFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public function getFileProperty(): string
    {
        return 'document';
    }

    public static function getClass(): string
    {
        return TrackingFile::class;
    }

    public function getDirectory(): string
    {
        return 'parts/trackings';
    }
}
