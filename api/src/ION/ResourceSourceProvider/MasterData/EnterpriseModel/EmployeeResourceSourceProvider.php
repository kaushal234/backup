<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\MasterData\EnterpriseModel;

use App\ION\Resources\MasterData\EnterpriseModel\Employee;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class EmployeeResourceSourceProvider implements ResourceSourceProviderInterface
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
        return 'Employee_v2';
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
        return Employee::class === $class;
    }
}
