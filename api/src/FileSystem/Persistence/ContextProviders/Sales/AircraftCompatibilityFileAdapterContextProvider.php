<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Sales;

use App\Entity\Sales\AircraftCompatibility\AircraftCompatibilityFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class AircraftCompatibilityFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return AircraftCompatibilityFile::class;
    }

    public function getDirectory(): string
    {
        return 'sales/aircraft_compatibility';
    }

    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            '%s-%s',
            null !== $context['createdAt'] ? $context['createdAt']->format('YmdHis') : date('YmdHis'),
            mb_substr(Utf8Slugger::slugify($context['filename']), 0, (int) $context['nameLength'])
        );
    }

    protected function getMimeTypes(): array
    {
        return ['application/pdf'];
    }
}
