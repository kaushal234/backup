<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence;

use App\FileSystem\AdapterFactory;
use App\FileSystem\Storage\FileStorageHandlerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PersistableFileManagerFactory
{
    private readonly EntityManagerInterface $em;
    private readonly FileStorageHandlerFactory $storageHandlerFactory;
    private readonly AdapterFactory $adapterFactory;
    private readonly ParameterBagInterface $parameters;

    public function __construct(EntityManagerInterface $em, FileStorageHandlerFactory $storageHandlerFactory, AdapterFactory $adapterFactory, ParameterBagInterface $parameters)
    {
        $this->em = $em;
        $this->storageHandlerFactory = $storageHandlerFactory;
        $this->adapterFactory = $adapterFactory;
        $this->parameters = $parameters;
    }

    public function getManagerForClass(string $class): PersistableFileManager
    {
        /** @var FileAdapter $adapter */
        $adapter = $this->adapterFactory->getAdapterForClass(FileAdapter::class, $class);

        return new PersistableFileManager(
            $this->em,
            $this->storageHandlerFactory->getStorageHandlerForClass($class),
            $adapter,
            $this->parameters->get('legacy.upload_dir')
        );
    }
}
