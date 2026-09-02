<?php

declare(strict_types=1);

namespace App\Jira\ResourceSourceProvider;

use App\Jira\Resources\TracteasyIssueType;

class TracteasyIssueTypeResourceSourceProvider extends IssueTypeResourceSourceProvider
{
    public function supports(string $class): bool
    {
        return TracteasyIssueType::class === $class;
    }
}
