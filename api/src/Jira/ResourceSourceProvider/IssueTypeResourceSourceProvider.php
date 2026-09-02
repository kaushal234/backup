<?php

declare(strict_types=1);

namespace App\Jira\ResourceSourceProvider;

use App\Jira\Resources\IssueType;

class IssueTypeResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return 'issuetype';
    }

    public function getCollectionReadOperation(): ?string
    {
        return 'issuetype/project';
    }

    public function getWriteOperation(): ?string
    {
        return null;
    }

    public function getUrlOptions(array $context): array
    {
        return $context['filters'] ?? [];
    }

    public function supports(string $class): bool
    {
        return IssueType::class === $class;
    }

    public function getMainClass(): ?string
    {
        return null;
    }
}
