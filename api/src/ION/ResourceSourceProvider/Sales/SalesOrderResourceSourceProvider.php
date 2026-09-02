<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\Sales;

use App\ION\Resources\Sales\SalesOrder;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class SalesOrderResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return 'Show';
    }

    public function getCollectionReadOperation(): ?string
    {
        return null;
    }

    public function getResource(): ?string
    {
        return 'SalesOrder';
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
        return SalesOrder::class === $class;
    }
}
