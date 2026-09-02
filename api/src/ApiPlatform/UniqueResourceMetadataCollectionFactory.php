<?php

declare(strict_types=1);

namespace App\ApiPlatform;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;

class UniqueResourceMetadataCollectionFactory
{
    public function __construct(
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
    ) {
    }

    public function getApiResource(string $resourceClass): ApiResource
    {
        return $this->resourceMetadataCollectionFactory->create($resourceClass)->getIterator()->current();
    }

    public function getExtraProperties(string $resourceClass): array
    {
        return $this->getApiResource($resourceClass)->getExtraProperties();
    }
}
