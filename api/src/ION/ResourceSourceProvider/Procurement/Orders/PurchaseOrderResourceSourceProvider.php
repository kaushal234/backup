<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\Procurement\Orders;

use App\ION\Resources\Procurement\Orders\PurchaseOrder;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class PurchaseOrderResourceSourceProvider implements ResourceSourceProviderInterface
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
        return 'txPurchaseOrder';
    }

    public function getUpdateOperation(): ?string
    {
        return 'txChange';
    }

    public function getCreateOperation(): ?string
    {
        return null;
    }

    public function getDataAreaFilter(): array
    {
        return PurchaseOrder::DATAAREA_FILTERS;
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
        return PurchaseOrder::class === $class;
    }
}
