<?php

declare(strict_types=1);

namespace App\FileSystem;

use App\Entity\Vault;
use App\Factory\FileResponseFactory;
use App\Factory\VaultFileDownloadableInterface;
use App\Repository\VaultRepository;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\Exception\InvalidArgumentException;

class VaultPartFileProvider
{
    // Max size 500MB for direct access (display and download)
    private const MAX_FILE_SIZE = 524288000;

    // Max size 100MB for files to be added in a ZIP archive
    private const MAX_FILE_SIZE_FOR_ARCHIVE = 104857600;

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly VaultRepository $vaultRepository,
        private readonly string $mountDirectory,
    ) {
    }

    public function getPartFileStreamResponse(VaultFileDownloadableInterface $data, FileResponseFactory $fileResponseFactory): BinaryFileResponse
    {
        try {
            $file = $this->getPartFile($data);
            if (!$file instanceof File) {
                throw new FileNotFoundException('empty file');
            }

            $response = $fileResponseFactory->createFileResponse($file);
            $response->setContentDisposition('inline', $file->getFilename());
        } catch (FileNotFoundException|FileException|InvalidArgumentException $e) {
            throw new NotFoundHttpException('Attached file could not be found.');
        }

        return $response;
    }

    public function get3DPartFileStreamResponse(VaultFileDownloadableInterface $data, FileResponseFactory $fileResponseFactory): BinaryFileResponse
    {
        try {
            $file = $this->getFile($data->getSite(), $data->getPartNumber(), $data->getRevision(), 'zip');
            if (!$file instanceof File) {
                throw new FileNotFoundException('empty file');
            }

            $response = $fileResponseFactory->createFileResponse($file);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $file->getFilename());
        } catch (FileNotFoundException|FileException|InvalidArgumentException $e) {
            throw new NotFoundHttpException('Attached file could not be found.');
        }

        return $response;
    }

    public function getPartFile(VaultFileDownloadableInterface $data, ?string $extension = null, bool $forArchive = false): ?File
    {
        if (null !== $data->getDrawing()) {
            return $this->getFileByName($data->getSite(), $data->getDrawing(), $extension, $forArchive);
        }

        return $this->getFile($data->getSite(), $data->getPartNumber(), $data->getRevision(), $extension ?? DrawingExtensionFactory::getExtension($data->getSignalCode()), $forArchive);
    }

    public function getFile(int $site, string $partNumber, ?string $revision = null, string $extension = 'jpg', bool $forArchive = false): ?File
    {
        if (null === $vaultConfiguration = $this->vaultRepository->findOneBySite($site)) {
            return null;
        }

        $revision = mb_trim(str_replace('.', '', $revision ?? ''));
        $revision = ('REL' !== $revision && !empty($revision)) ? "_$revision" : '';

        $filenameWithoutExtension = mb_trim($partNumber).$revision;

        $filename = $filenameWithoutExtension.'.'.mb_strtolower($extension);
        $path = $this->getPath($vaultConfiguration, $filename);

        if ($this->filesystem->exists($path) && $this->isFileSizeValid($path, $forArchive)) {
            return new File($path, false);
        }

        $filename = $filenameWithoutExtension.'.'.mb_strtoupper($extension);
        $path = $this->getPath($vaultConfiguration, $filename);

        if ($this->filesystem->exists($path) && $this->isFileSizeValid($path, $forArchive)) {
            return new File($path, false);
        }

        $filename = $filenameWithoutExtension.'.'.mb_strtolower($extension);
        $path = $this->getDefaultPath($vaultConfiguration, $filename);

        if ($this->filesystem->exists($path) && $this->isFileSizeValid($path, $forArchive)) {
            return new File($path, false);
        }

        $filename = $filenameWithoutExtension.'.'.mb_strtoupper($extension);
        $path = $this->getDefaultPath($vaultConfiguration, $filename);

        if ($this->filesystem->exists($path) && $this->isFileSizeValid($path, $forArchive)) {
            return new File($path, false);
        }

        return null;
    }

    public function getFileByName(int $site, string $filename, ?string $extension = null, bool $forArchive = false): ?File
    {
        if (null === $vaultConfiguration = $this->vaultRepository->findOneBySite($site)) {
            return null;
        }

        $drawingExtension = pathinfo($filename, \PATHINFO_EXTENSION);
        $expectedExtension = $extension ?? $drawingExtension;

        $filenameWithoutExtension = mb_rtrim($filename, $drawingExtension);
        if (!empty($expectedExtension) && '.' !== mb_substr($filenameWithoutExtension, -1, 1)) {
            $filenameWithoutExtension = \sprintf('%s.', $filename);
        }

        $filename = \sprintf('%s%s', $filenameWithoutExtension, mb_strtolower($expectedExtension));
        $path = \sprintf('%s/%s', $this->getFolder($vaultConfiguration, $filename), $filename);

        if ($this->filesystem->exists($path) && $this->isFileSizeValid($path, $forArchive)) {
            return new File($path, false);
        }

        $path = $this->getDefaultPath($vaultConfiguration, $filename);

        if ($this->filesystem->exists($path) && $this->isFileSizeValid($path, $forArchive)) {
            return new File($path, false);
        }

        $filename = \sprintf('%s%s', $filenameWithoutExtension, mb_strtoupper($expectedExtension));
        $path = \sprintf('%s/%s', $this->getFolder($vaultConfiguration, $filename), $filename);

        if ($this->filesystem->exists($path) && $this->isFileSizeValid($path, $forArchive)) {
            return new File($path, false);
        }

        $path = $this->getDefaultPath($vaultConfiguration, $filename);

        if ($this->filesystem->exists($path) && $this->isFileSizeValid($path, $forArchive)) {
            return new File($path, false);
        }

        return null;
    }

    private function getFolder(Vault $vaultConfiguration, string $partNumber): string
    {
        $folder = mb_substr($partNumber, 0, $vaultConfiguration->folder);
        $subFolder = mb_substr($partNumber, 0, $vaultConfiguration->subFolder);

        return \sprintf('%s/%s/%s/%s', $this->mountDirectory, $vaultConfiguration->path, $folder, $subFolder);
    }

    private function getPath(Vault $vaultConfiguration, string $fileName): string
    {
        return \sprintf('%s/%s', $this->getFolder($vaultConfiguration, $fileName), $fileName);
    }

    private function getDefaultPath(Vault $vaultConfiguration, string $fileName): string
    {
        return $this->mountDirectory.'/'.$vaultConfiguration->path.'/'.$fileName;
    }

    private function isFileSizeValid(string $path, bool $forArchive = false): bool
    {
        $fileSize = filesize($path);
        $maxSize = $forArchive ? self::MAX_FILE_SIZE_FOR_ARCHIVE : self::MAX_FILE_SIZE;

        return false !== $fileSize && $fileSize <= $maxSize;
    }
}
