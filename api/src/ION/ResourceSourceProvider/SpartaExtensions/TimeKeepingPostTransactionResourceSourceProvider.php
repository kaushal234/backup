<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\SpartaExtensions;

use App\ION\Dto\SpartaExtensions\Times\TimeKeepingPostTransactionInput;
use App\ION\Resources\SpartaExtensions\Times\TimeKeepingPostTransaction;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class TimeKeepingPostTransactionResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return null;
    }

    public function getCollectionReadOperation(): ?string
    {
        return null;
    }

    public function getResource(): ?string
    {
        return 'HoursAccounting_TLD';
    }

    public function getUpdateOperation(): ?string
    {
        return null;
    }

    public function getCreateOperation(): ?string
    {
        return 'PostHours';
    }

    public function getDataAreaFilter(): array
    {
        return [];
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
        return \in_array($class, [TimeKeepingPostTransaction::class, TimeKeepingPostTransactionInput::class], true);
    }
}
