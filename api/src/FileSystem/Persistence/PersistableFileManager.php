<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence;

use App\Entity\File as ApiFile;
use App\FileSystem\Storage\FileStorageHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;

class PersistableFileManager
{
    private readonly EntityManagerInterface $entityManager;
    private readonly FileStorageHandlerInterface $storageHandler;
    private readonly FileAdapter $adapter;
    private readonly string $legacyUploadDir;

    public function __construct(EntityManagerInterface $entityManager, FileStorageHandlerInterface $storageHandler, FileAdapter $adapter, string $legacyUploadDir)
    {
        $this->entityManager = $entityManager;
        $this->storageHandler = $storageHandler;
        $this->adapter = $adapter;
        $this->legacyUploadDir = $legacyUploadDir;
    }

    public function attach($attachable, File $file, array $metadata = []): object
    {
        $filename = $this->adapter->attach($attachable, $file, $metadata);

        $obsoleteFile = $metadata['obsolete_file'] ?? null;
        if ($obsoleteFile instanceof ApiFile) {
            $this->storageHandler->remove($this->legacyUploadDir.'/'.$obsoleteFile->getFilePath());
        }

        $this->storageHandler->save($filename, $file->openFile());

        $this->entityManager->persist($attachable);

        try {
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            $this->storageHandler->remove($filename);
            throw $exception;
        }

        $this->entityManager->refresh($attachable);

        return $attachable;
    }

    public function detach($attachable, ?File $file = null)
    {
        $this->adapter->detach($attachable, $file);

        $this->entityManager->persist($attachable);
        $this->entityManager->flush();

        if (null !== $file) {
            $this->storageHandler->remove($file->getPath().'/'.$file->getFilename());
        }
    }

    public function getAdapter(): FileAdapter
    {
        return $this->adapter;
    }
}
