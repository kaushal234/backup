<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\Manufacturing;

use App\ION\Resources\Manufacturing\BillOfMaterialsSecurity;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class BillOfMaterialsSecurityResourceSourceProvider implements ResourceSourceProviderInterface
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
        return 'txBillOfMaterialsSecurity';
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
        return ['depth' => 99];
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
        return BillOfMaterialsSecurity::class === $class;
    }
}
