<?php

declare(strict_types=1);

namespace App\FileSystem\Storage;

use Symfony\Component\Filesystem\Filesystem;

class FileStorageHandler implements FileStorageHandlerInterface
{
    private readonly Filesystem $filesystem;

    private readonly string $storageDirectory;

    /**
     * @param string $storageDirectory
     */
    public function __construct(Filesystem $filesystem, $storageDirectory)
    {
        $this->filesystem = $filesystem;
        $this->storageDirectory = $storageDirectory;

        $this->filesystem->mkdir($this->storageDirectory);
    }

    public function save(string $filename, \SplFileObject $filecontent)
    {
        $this->filesystem->dumpFile((string) $this->getFullPath($filename), (string) $filecontent->fread($filecontent->getSize()));
    }

    public function copy(string $filename, \SplFileObject $filecontent): void
    {
        $this->filesystem->copy((string) $filecontent->getRealPath(), $this->getFullPath($filename));
    }

    public function remove(?string $filepath)
    {
        if (null === $filepath) {
            return null;
        }

        $path = $this->getFullPath($filepath);

        if (!is_file($path)) {
            return;
        }

        $this->filesystem->remove($path);
    }

    private function getFullPath(?string $filename): ?string
    {
        if (null === $filename) {
            return null;
        }

        return (0 === mb_strpos($filename, $this->storageDirectory)) ? $filename : $this->storageDirectory.'/'.$filename;
    }
}
