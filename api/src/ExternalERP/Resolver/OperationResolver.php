<?php

declare(strict_types=1);

namespace App\ExternalERP\Resolver;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\ION\SourceProvider\SourceProvider;

final readonly class OperationResolver implements OperationResolverInterface
{
    public function __construct(
        private SourceProvider $sourceProvider,
    ) {
    }

    public function resolve(Operation $operation, array $uriVariables = []): string
    {
        $resourceSourceProvider = $this->getResourceSourceProvider($operation);

        return $operation instanceof Get
            ? $resourceSourceProvider->getRestItemReadOperation($uriVariables)
            : $resourceSourceProvider->getRestCollectionReadOperation($uriVariables);
    }

    private function getResourceSourceProvider(Operation $operation): ResourceSourceProviderInterface
    {
        return $this->sourceProvider->getResourceSourceProvider($operation->getClass());
    }
}
