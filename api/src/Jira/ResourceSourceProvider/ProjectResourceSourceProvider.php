<?php

declare(strict_types=1);

namespace App\Jira\ResourceSourceProvider;

use App\Jira\Resources\Project;

class ProjectResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): string
    {
        return 'project';
    }

    public function getCollectionReadOperation(): string
    {
        return 'project';
    }

    public function getWriteOperation(): string
    {
        return 'project';
    }

    public function supports(string $class): bool
    {
        return Project::class === $class;
    }

    public function getUrlOptions(array $context = []): array
    {
        return [];
    }

    public function getMainClass(): null
    {
        return null;
    }
}
