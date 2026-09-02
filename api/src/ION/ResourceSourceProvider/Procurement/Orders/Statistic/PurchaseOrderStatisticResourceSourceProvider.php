<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\Procurement\Orders\Statistic;

use App\ION\Resources\Procurement\Orders\Statistic\PurchaseOrderStatistic;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class PurchaseOrderStatisticResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return null;
    }

    public function getCollectionReadOperation(): ?string
    {
        return 'txLateAndUnconfirmedLines';
    }

    public function getResource(): ?string
    {
        return 'txPurchaseOrder';
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
        return PurchaseOrderStatistic::class === $class;
    }
}
