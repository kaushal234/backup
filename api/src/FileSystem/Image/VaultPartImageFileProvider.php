<?php

declare(strict_types=1);

namespace App\FileSystem\Image;

use App\Entity\Vault;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

/**
 * This service contains methods to find pictures of parts numbers in the vault parts picture volume.
 */
class VaultPartImageFileProvider
{
    protected const int FOLDER_LENGTH = 2;
    protected const int SUBFOLDER_LENGTH = 4;
    protected const array EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly string $mountDirectory,
        private readonly string $vaultPartsImagesPath,
    ) {
    }

    /**
     * Get all pictures of part number.
     * Looking for the main file and extra files.
     */
    public function getAll(string $partNumber): array
    {
        return array_filter([
            ...[$this->getMainPicture($partNumber)],
            ...$this->getExtraPictures($partNumber),
        ]);
    }

    /**
     * Get all additional files of part number.
     * Looking in the folder with the part number name.
     * Ex: PN#62072, looking for /62/6207/62072/*.jpg.
     */
    public function getExtraPictures(string $partNumber): array
    {
        $files = [];
        $partFolderPath = Path::join($this->getFolderPath($partNumber), $partNumber);
        $finder = new Finder();

        if (false === is_dir($partFolderPath)) {
            return $files;
        }

        $finder
            ->files()
            ->in($partFolderPath)
            ->name(array_map(static fn ($extension) => \sprintf('/\.%s/i', $extension), self::EXTENSIONS));

        foreach ($finder as $file) {
            $files[] = new File($file->getRealPath(), false);
        }

        return $files;
    }

    /**
     * Get the one unique file with the part number as a filename.
     * Ex: PN#62072, looking for /62/6207/62072.jpg.
     */
    public function getMainPicture(string $partNumber): ?File
    {
        $path = $this->getFolderPath($partNumber);
        $finder = new Finder();

        if (false === is_dir($path)) {
            return null;
        }

        $finder
            ->files()
            ->in($path)
            ->name(array_map(
                static fn ($extension) => \sprintf('/%s\.%s/i', $partNumber, $extension),
                self::EXTENSIONS
            ));

        foreach ($finder as $file) {
            return new File($file->getRealPath(), false);
        }

        return null;
    }

    /**
     * Get a folder path of part number.
     * Ex: PN#62072, {MOUNT_DIRECTORY}/62/6207/.
     */
    protected function getFolderPath(string $partNumber): string
    {
        if (mb_strlen($partNumber) < self::SUBFOLDER_LENGTH) {
            throw new \InvalidArgumentException(\sprintf('Part number %s must be at least %d characters long.', $partNumber, self::SUBFOLDER_LENGTH));
        }

        // Get the folder and subfolder depending on part number.
        $folder = mb_substr($partNumber, 0, self::FOLDER_LENGTH);
        $subFolder = mb_substr($partNumber, 0, self::SUBFOLDER_LENGTH);

        return Path::join($this->mountDirectory, $this->vaultPartsImagesPath, $folder, $subFolder);
    }
}
