<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders;

use App\FileSystem\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Image;

abstract class AbstractImageFileAdapterContextProvider implements FileAdapterContextProviderInterface
{
    protected ImageManager $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function processFile(File $file, array $metadata = [])
    {
        $fileContent = $this->imageManager->resize($file->openFile(), 1_200);

        $file->openFile('w')->fwrite($fileContent);
    }

    public function getFileConstraint($subject): Constraint
    {
        $constraint = new Image();
        $constraint->mimeTypes = $this->getMimeTypes();
        $constraint->maxSize = $this->getMaxSize();

        return $constraint;
    }

    protected function getMimeTypes(): array
    {
        return ['image/jpeg', 'image/png'];
    }

    protected function getMaxSize(): string
    {
        return '3M';
    }
}
