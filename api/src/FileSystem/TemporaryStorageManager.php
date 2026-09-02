<?php

declare(strict_types=1);

namespace App\FileSystem;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Create folder tmp on specified folder
 * A listener on event kernel.terminate will delete this folder.
 */
class TemporaryStorageManager
{
    private const TEMPORARY_FOLDER = 'tmp';

    public function __construct(
        private readonly string $temporaryFolder,
        private readonly Filesystem $filesystem)
    {
    }

    public function createTemporaryFileFromContent(string $fileContent, string $extension): string
    {
        $filename = uniqid('', true);
        $path = \sprintf('%s/%s/%s.%s', $this->temporaryFolder, self::TEMPORARY_FOLDER, $filename, $extension);

        $this->filesystem->dumpFile($path, $fileContent);

        return $path;
    }

    public function removeFolder(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove(\sprintf('%s/%s', $this->temporaryFolder, self::TEMPORARY_FOLDER));
    }
}
