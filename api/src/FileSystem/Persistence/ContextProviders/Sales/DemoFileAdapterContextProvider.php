<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Sales;

use App\Entity\Sales\DemoFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;

class DemoFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return DemoFile::class;
    }

    public function getFileProperty(): string
    {
        return 'demoFiles';
    }

    public function getDirectory(): string
    {
        return 'sales/demos';
    }
}
