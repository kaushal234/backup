<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Sales;

use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerLogoFile;
use App\FileSystem\Persistence\ContextProviders\AbstractImageFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;
use Symfony\Component\HttpFoundation\File\File;

class CustomerLogoFileAdapterContextProvider extends AbstractImageFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return CustomerLogoFile::class;
    }

    public function getFileProperty(): string
    {
        return 'logo';
    }

    public function getDirectory(): string
    {
        return 'customers';
    }

    public function processFile(File $file, array $metadata = [])
    {
        $fileContent = $this->imageManager->resize($file->openFile(), 300, 300);

        $file->openFile('w')->fwrite($fileContent);
    }

    /** @param Customer $subject */
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
