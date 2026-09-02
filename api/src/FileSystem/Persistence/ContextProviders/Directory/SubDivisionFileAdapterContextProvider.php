<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Directory;

use App\Entity\Directory\SubDivision;
use App\Entity\Directory\SubDivisionFile;
use App\FileSystem\Persistence\ContextProviders\AbstractImageFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;
use Symfony\Component\HttpFoundation\File\File;

class SubDivisionFileAdapterContextProvider extends AbstractImageFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return SubDivisionFile::class;
    }

    public function getDirectory(): string
    {
        return 'directory/subdivisions';
    }

    public function processFile(File $file, array $metadata = [])
    {
        $fileContent = $this->imageManager->resize($file->openFile(), 480, 480);

        $file->openFile('w')->fwrite($fileContent);
    }

    public function getFileProperty(): string
    {
        return 'logo';
    }

    /**
     * @param SubDivision $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            '%s.%s',
            Utf8Slugger::uniqueSlugify($subject->name),
            $context['extension']
        );
    }
}
