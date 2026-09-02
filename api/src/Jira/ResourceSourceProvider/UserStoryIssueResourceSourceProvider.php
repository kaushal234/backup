<?php

declare(strict_types=1);

namespace App\Jira\ResourceSourceProvider;

use App\Entity\Module\Specification\UserStory;
use App\Jira\Resources\UserStoryIssue;

class UserStoryIssueResourceSourceProvider extends IssueResourceSourceProvider
{
    public function getMainClass(): ?string
    {
        return UserStory::class;
    }

    public function supports(string $class): bool
    {
        return UserStoryIssue::class === $class;
    }
}
