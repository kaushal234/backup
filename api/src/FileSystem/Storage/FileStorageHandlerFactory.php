<?php

declare(strict_types=1);

namespace App\FileSystem\Storage;

use App\FileSystem\Persistence\ContextProviders\FileAdapterContextProviderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class FileStorageHandlerFactory
{
    private readonly Filesystem $filesystem;

    private readonly ContainerInterface $locator;

    private readonly ParameterBagInterface $parameters;

    public function __construct(Filesystem $filesystem, ParameterBagInterface $parameters, ContainerInterface $fileAdapterContextProviders)
    {
        $this->filesystem = $filesystem;
        $this->locator = $fileAdapterContextProviders;
        $this->parameters = $parameters;
    }

    public function getStorageHandlerForClass(string $class): FileStorageHandlerInterface
    {
        $directory = '';
        if ($this->locator->has($class)) {
            /** @var FileAdapterContextProviderInterface $contextProvider */
            $contextProvider = $this->locator->get($class);
            $directory = $contextProvider->getDirectory();
        }

        $persistedDate = (new \DateTime())->format('Y/m');

        return new FileStorageHandler($this->filesystem, $this->parameters->get('legacy.upload_dir').'/'.$directory.'/'.$persistedDate);
    }
}
