<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\Manufacturing\ProductionOrder;

use App\ION\Resources\Manufacturing\ProductionOrder\ProductionOrder;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class ProductionOrderResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return null;
    }

    public function getCollectionReadOperation(): ?string
    {
        return null;
    }

    public function getResource(): ?string
    {
        return 'txProductionOrder';
    }

    public function getUpdateOperation(): ?string
    {
        return null;
    }

    public function getCreateOperation(): ?string
    {
        return 'txUpdateCrabs';
    }

    public function getDataAreaFilter(): array
    {
        return [];
    }

    public function deserializeAfterPersist(): bool
    {
        return true;
    }

    public function getRestItemReadOperation(array $uriVariables = []): ?string
    {
        return null;
    }

    public function getRestCollectionReadOperation(array $uriVariables = []): ?string
    {
        return null;
    }

    public function getFilters(): array
    {
        return [];
    }

    public function supports(string $class): bool
    {
        return ProductionOrder::class === $class;
    }
}
