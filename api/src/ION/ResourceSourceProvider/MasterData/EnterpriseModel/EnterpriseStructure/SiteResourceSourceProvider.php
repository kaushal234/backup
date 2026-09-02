<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\MasterData\EnterpriseModel\EnterpriseStructure;

use App\ION\Resources\MasterData\EnterpriseModel\EnterpriseStructure\Site;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class SiteResourceSourceProvider implements ResourceSourceProviderInterface
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
        return 'Site_ES';
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
        return Site::class === $class;
    }
}
