<?php

declare(strict_types=1);

namespace App\Sdk\Http;

use App\Sdk\Exception\NoSupportiveResourceSourceProviderException;
use App\Sdk\Http\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\Sdk\Resource\ResourceInterface;

final class SourceProvider implements SourceProviderInterface
{
    /**
     * @param list<ResourceSourceProviderInterface<ResourceInterface>> $providers
     */
    public function __construct(
        private array $providers = [],
    ) {
    }

    /**
     * @param ResourceSourceProviderInterface<ResourceInterface> $provider
     */
    public function addResourceSourceProvider(ResourceSourceProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * {@inheritDoc}
     */
    public function getFindSources(string $resource, string|array $identifier): array
    {
        $sources = [];
        foreach ($this->getCompatibleProviders($resource) as $provider) {
            $source = $provider->getFindSource($identifier);
            if (null !== $source) {
                $sources[] = $source;
            }
        }

        return $sources;
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdateSources(string $resource, array|string $identifier, array $update): array
    {
        $sources = [];
        foreach ($this->getCompatibleProviders($resource) as $provider) {
            $source = $provider->getUpdateSource($identifier, $update);
            if (null !== $source) {
                $sources[] = $source;
            }
        }

        return $sources;
    }

    /**
     * {@inheritDoc}
     */
    public function getDownloadSources(string $resource, string|array $identifier): array
    {
        $sources = [];
        foreach ($this->getCompatibleProviders($resource) as $provider) {
            $source = $provider->getDownloadSource($identifier);
            if (null !== $source) {
                $sources[] = $source;
            }
        }

        return $sources;
    }

    /**
     * {@inheritDoc}
     */
    public function getFindAllSources(string $resource, array $criteria = []): array
    {
        $sources = [];
        foreach ($this->getCompatibleProviders($resource) as $provider) {
            $source = $provider->getFindAllSource($criteria);
            if (null !== $source) {
                $sources[] = $source;
            }
        }

        /** @var non-empty-list<HttpSource|DistributedHttpSource> */
        return $sources;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaginationSources(string $resource, int $page, int $itemsPerPage, array $criteria = []): array
    {
        $sources = [];
        foreach ($this->getCompatibleProviders($resource) as $provider) {
            $source = $provider->getPaginationSource($page, $itemsPerPage, $criteria);
            if (null !== $source) {
                $sources[] = $source;
            }
        }

        return $sources;
    }

    /**
     * {@inheritDoc}
     */
    public function getExcelSources(string $resource, array $criteria = []): array
    {
        $sources = [];
        foreach ($this->getCompatibleProviders($resource) as $provider) {
            $source = $provider->getExcelSource($criteria);
            if (null !== $source) {
                $sources[] = $source;
            }
        }

        return $sources;
    }

    /**
     * @template T of ResourceInterface
     *
     * @param class-string<T> $resource
     *
     * @return non-empty-list<ResourceSourceProviderInterface<T>>
     *
     * @throws NoSupportiveResourceSourceProviderException if no compatible resource provider is found
     */
    private function getCompatibleProviders(string $resource): array
    {
        $providers = [];
        foreach ($this->providers as $provider) {
            if ($provider->supports($resource)) {
                /** @var ResourceSourceProviderInterface<T> $provider */
                $providers[] = $provider;
            }
        }

        if ([] === $providers) {
            throw NoSupportiveResourceSourceProviderException::forResource($resource);
        }

        return $providers;
    }
}
