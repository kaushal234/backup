<?php

declare(strict_types=1);

namespace App\FileSystem;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class AdapterFactory implements ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $adapterContextProviders,
        protected ContainerInterface $locator)
    {
    }

    public function getAdapterForClass(string $adapterClass, string $class): AdapterInterface
    {
        if (!$this->adapterContextProviders->has($class)) {
            throw new \InvalidArgumentException(\sprintf('No context provider found for %s', $class));
        }

        return new $adapterClass(
            $this->adapterContextProviders->get($class),
            $this->locator
        );
    }

    public static function getSubscribedServices(): array
    {
        return AbstractAdapter::getSubscribedServices();
    }
}
