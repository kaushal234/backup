<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\Planning\OrderPlanning;

use App\ION\Resources\Planning\OrderPlanning\PlannedOrder;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class PlannedOrderResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return null;
    }

    public function getCollectionReadOperation(): ?string
    {
        return 'txList';
    }

    public function getResource(): ?string
    {
        return 'txPlannedOrders';
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
        return PlannedOrder::DATAAREA_FILTERS;
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
        return PlannedOrder::class === $class;
    }
}
