<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Persistence;

use Kreyu\Bundle\DataTableBundle\Persistence\PersistenceSubjectInterface;

readonly class UserModulePersistenceSubjectAggregate implements PersistenceSubjectInterface
{
    public function __construct(
        private string $userId,
        private string $moduleName,
    ) {
    }

    public function getDataTablePersistenceIdentifier(): string
    {
        return $this->userId;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }
}
