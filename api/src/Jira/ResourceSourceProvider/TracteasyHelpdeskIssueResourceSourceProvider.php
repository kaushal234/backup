<?php

declare(strict_types=1);

namespace App\Jira\ResourceSourceProvider;

use App\Entity\Service\TechnicianOnCall;
use App\Jira\Resources\TracteasyHelpdeskIssue;

class TracteasyHelpdeskIssueResourceSourceProvider extends IssueResourceSourceProvider
{
    public function getMainClass(): ?string
    {
        return TechnicianOnCall::class;
    }

    public function supports(string $class): bool
    {
        return TracteasyHelpdeskIssue::class === $class;
    }
}
