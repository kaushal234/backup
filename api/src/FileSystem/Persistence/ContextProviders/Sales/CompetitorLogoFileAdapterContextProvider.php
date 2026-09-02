<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Sales;

use App\Entity\Sales\Competitor;
use App\Entity\Sales\CompetitorLogoFile;
use App\FileSystem\Persistence\ContextProviders\AbstractImageFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;
use Symfony\Component\HttpFoundation\File\File;

class CompetitorLogoFileAdapterContextProvider extends AbstractImageFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return CompetitorLogoFile::class;
    }

    public function getFileProperty(): string
    {
        return 'logo';
    }

    public function getDirectory(): string
    {
        return 'sales/competitors';
    }

    public function processFile(File $file, array $metadata = [])
    {
        $fileContent = $this->imageManager->resize($file->openFile(), 480, 480);

        $file->openFile('w')->fwrite($fileContent);
    }

    /** @param Competitor $subject */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            '%05d-%s.%s',
            $subject->getId(),
            Utf8Slugger::slugify($subject->getName()),
            'jpg'
        );
    }
}
