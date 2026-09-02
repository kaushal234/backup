<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Directory;

use App\Entity\Directory\People;
use App\Entity\Directory\PeopleFile;
use App\FileSystem\Persistence\ContextProviders\AbstractImageFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;
use Symfony\Component\HttpFoundation\File\File;

class PeopleFileAdapterContextProvider extends AbstractImageFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return PeopleFile::class;
    }

    public function getDirectory(): string
    {
        return 'photos';
    }

    public function processFile(File $file, array $metadata = [])
    {
        $fileContent = $this->imageManager->resize($file->openFile(), 480, 480);

        $file->openFile('w')->fwrite($fileContent);
    }

    public function getFileProperty(): string
    {
        return 'photo';
    }

    /**
     * @param People $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            '%s.%s',
            Utf8Slugger::uniqueSlugify($subject->getEmail()),
            'jpg'
        );
    }
}
