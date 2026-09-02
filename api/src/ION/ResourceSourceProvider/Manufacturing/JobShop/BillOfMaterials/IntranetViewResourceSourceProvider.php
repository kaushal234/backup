<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\Manufacturing\JobShop\BillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\IntranetView;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class IntranetViewResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return 'txBom';
    }

    public function getCollectionReadOperation(): ?string
    {
        return null;
    }

    public function getResource(): ?string
    {
        return 'txCustomizedBillOfMaterials_v2';
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
        return IntranetView::class === $class;
    }
}
