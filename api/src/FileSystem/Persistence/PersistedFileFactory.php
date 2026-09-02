<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence;

use App\Entity\File;
use App\FileSystem\FileHashGenerator;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class PersistedFileFactory
{
    private readonly FileHashGenerator $fileHashGenerator;

    public function __construct(FileHashGenerator $fileHashGenerator)
    {
        $this->fileHashGenerator = $fileHashGenerator;
    }

    public function create(SymfonyFile $sfFile, File $file): File
    {
        $file
            ->setExtension($sfFile->guessExtension() ?? $sfFile->getExtension())
            ->setMimeType($sfFile->getMimeType())
            ->setSize($sfFile->getSize())
            ->setSha($this->fileHashGenerator->hash($sfFile))
        ;

        return $file;
    }
}
