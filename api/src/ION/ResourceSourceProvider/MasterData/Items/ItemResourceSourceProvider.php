<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\MasterData\Items;

use App\ION\Resources\MasterData\Items\Item;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class ItemResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return 'txShow';
    }

    public function getCollectionReadOperation(): ?string
    {
        return 'txList';
    }

    public function getResource(): ?string
    {
        return 'txItemsBySite';
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
        return [];
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
        return Item::class === $class;
    }
}
