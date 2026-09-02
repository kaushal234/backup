<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\MasterData\BusinessPartners;

use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContact;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class BusinessPartnerContactResourceSourceProvider implements ResourceSourceProviderInterface
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
        return 'Contact_v3';
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
        return BusinessPartnerContact::class === $class;
    }
}
