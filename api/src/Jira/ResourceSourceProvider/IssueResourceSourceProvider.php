<?php

declare(strict_types=1);

namespace App\Jira\ResourceSourceProvider;

use App\Jira\Resources\Issue;

class IssueResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return 'issue';
    }

    public function getCollectionReadOperation(): ?string
    {
        return null;
    }

    public function getWriteOperation(): ?string
    {
        return 'issue';
    }

    public function getUrlOptions(array $context): array
    {
        return [];
    }

    public function supports(string $class): bool
    {
        return Issue::class === $class;
    }

    public function getMainClass(): ?string
    {
        return null;
    }
}
