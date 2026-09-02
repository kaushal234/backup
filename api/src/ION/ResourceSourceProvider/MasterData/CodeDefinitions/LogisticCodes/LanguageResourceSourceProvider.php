<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\MasterData\CodeDefinitions\LogisticCodes;

use App\ION\Resources\MasterData\CodeDefinitions\LogisticCodes\Language;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class LanguageResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return null;
    }

    public function getCollectionReadOperation(): ?string
    {
        return 'txList';
    }

    public function getResource(): ?string
    {
        return 'txDataLanguages';
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
        return Language::class === $class;
    }
}
