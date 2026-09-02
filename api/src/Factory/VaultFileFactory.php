<?php

declare(strict_types=1);

namespace App\Factory;

use App\FileSystem\AdapterFactory;
use App\FileSystem\Persistence\FileAdapter;
use App\FileSystem\Storage\FileStorageHandlerFactory;
use App\FileSystem\VaultPartFileProvider;

class VaultFileFactory
{
    private readonly VaultPartFileProvider $vaultPartFileProvider;
    private readonly AdapterFactory $adapterFactory;
    private readonly FileStorageHandlerFactory $storageHandlerFactory;

    public function __construct(VaultPartFileProvider $vaultPartFileProvider, AdapterFactory $adapterFactory, FileStorageHandlerFactory $storageHandlerFactory)
    {
        $this->vaultPartFileProvider = $vaultPartFileProvider;
        $this->adapterFactory = $adapterFactory;
        $this->storageHandlerFactory = $storageHandlerFactory;
    }

    public function attach(VaultFileInterface $data, int $site, string $extension, bool $validate = true): ?string
    {
        if (null === $file = $this->vaultPartFileProvider->getFile($site, $data->getPartNumber(), $data->getRevision(), $extension)) {
            return null;
        }

        $metadata['validate'] = $validate;

        /** @var FileAdapter $adapter */
        $adapter = $this->adapterFactory->getAdapterForClass(FileAdapter::class, $data->getFileClass());

        return $adapter->attach($data, $file, $metadata);
    }

    public function store(VaultFileInterface $data, int $site, string $extension, bool $validate = true): bool
    {
        if (null === $file = $this->vaultPartFileProvider->getFile($site, $data->getPartNumber(), $data->getRevision(), $extension)) {
            return false;
        }

        $metadata['validate'] = $validate;

        /** @var FileAdapter $adapter */
        $adapter = $this->adapterFactory->getAdapterForClass(FileAdapter::class, $data->getFileClass());
        $filename = $adapter->attach($data, $file, $metadata);
        $this->storageHandlerFactory->getStorageHandlerForClass($data->getFileClass())->copy($filename, $file->openFile());

        return true;
    }
}
