<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\MasterData\Items;

use App\ION\Resources\MasterData\Items\ItemMonologistic;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class ItemMonologisticResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return 'Show';
    }

    public function getCollectionReadOperation(): ?string
    {
        return 'List';
    }

    public function getResource(): ?string
    {
        return 'Item_v3';
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
        return ItemMonologistic::class === $class;
    }
}
