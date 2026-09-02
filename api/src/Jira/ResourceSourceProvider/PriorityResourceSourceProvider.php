<?php

declare(strict_types=1);

namespace App\Jira\ResourceSourceProvider;

use App\Jira\Resources\Priority;

class PriorityResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return 'priority';
    }

    public function getCollectionReadOperation(): ?string
    {
        return 'priority';
    }

    public function getWriteOperation(): ?string
    {
        return null;
    }

    public function getUrlOptions(array $context): array
    {
        return [];
    }

    public function supports(string $class): bool
    {
        return Priority::class === $class;
    }

    public function getMainClass(): ?string
    {
        return null;
    }
}
