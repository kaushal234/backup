<?php

declare(strict_types=1);

namespace App\ExternalERP\Resolver;

use ApiPlatform\Metadata\Operation;

interface OperationResolverInterface
{
    public function resolve(Operation $operation, array $uriVariables = []): string;
}
