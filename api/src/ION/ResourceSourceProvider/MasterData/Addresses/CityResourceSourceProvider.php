<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\MasterData\Addresses;

use App\ION\Resources\MasterData\Addresses\City;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class CityResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return null;
    }

    public function getCollectionReadOperation(): ?string
    {
        return 'List';
    }

    public function getResource(): ?string
    {
        return 'txCities';
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
        return City::class === $class;
    }
}
