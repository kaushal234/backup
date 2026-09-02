<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\MasterData\Items\ItemClassification;

use App\ION\Resources\MasterData\Items\ItemClassification\ItemByVendor;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class ItemByVendorResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return 'txXRef';
    }

    public function getCollectionReadOperation(): ?string
    {
        return null;
    }

    public function getResource(): ?string
    {
        return 'txItem';
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
        return ItemByVendor::DATAAREA_FILTERS;
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
        return ItemByVendor::class === $class;
    }
}
