<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\MasterData\BusinessPartners;

use App\ION\Resources\MasterData\BusinessPartners\Turnover;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class TurnoverResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return null;
    }

    public function getCollectionReadOperation(): ?string
    {
        return 'txTurnover';
    }

    public function getResource(): ?string
    {
        return 'txBusinessPartner';
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
        return Turnover::class === $class;
    }
}
