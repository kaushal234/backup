<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\Warehousing;

use App\ION\Resources\Warehousing\Inventory;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class InventoryResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return 'txShow';
    }

    public function getCollectionReadOperation(): ?string
    {
        return null;
    }

    public function getResource(): ?string
    {
        return 'txInventory';
    }

    public function getUpdateOperation(): ?string
    {
        return null;
    }

    public function getCreateOperation(): ?string
    {
        return null;
    }

    public function getDataAreaFilter(): array
    {
        return Inventory::DATAAREA_FILTERS;
    }

    public function deserializeAfterPersist(): bool
    {
        return false;
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
        return Inventory::class === $class;
    }
}
