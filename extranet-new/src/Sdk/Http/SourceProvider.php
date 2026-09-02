<?php

declare(strict_types=1);

namespace App\Sdk\Http;

use App\Sdk\Exception\NoSupportiveResourceSourceProviderException;
use App\Sdk\Http\ResourceSourceProvider\ResourceSourceProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class SourceProvider
{
    /**
     * @param iterable<ResourceSourceProviderInterface> $resourceSourceProviders
     */
    public function __construct(
        #[AutowireIterator('resource.source_provider')] private readonly iterable $resourceSourceProviders,
    ) {
    }

    public function getResourceSourceProvider(string $resource): ResourceSourceProviderInterface
    {
        foreach ($this->resourceSourceProviders as $resourceSourceProvider) {
            if ($resourceSourceProvider->supports($resource)) {
                return $resourceSourceProvider;
            }
        }

        throw NoSupportiveResourceSourceProviderException::forResource($resource);
    }
}
