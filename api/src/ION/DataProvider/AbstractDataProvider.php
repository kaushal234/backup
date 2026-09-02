<?php

declare(strict_types=1);

namespace App\ION\DataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ExternalERP\Resolver\OperationResolverInterface;
use App\Http\LnClient;

abstract readonly class AbstractDataProvider implements ProviderInterface
{
    public function __construct(
        protected LnClient $client,
        protected OperationResolverInterface $operationResolver,
    ) {
    }

    protected function getOperation(Operation $operation, array $uriVariables = []): string
    {
        return $this->operationResolver->resolve($operation, $uriVariables);
    }
}
