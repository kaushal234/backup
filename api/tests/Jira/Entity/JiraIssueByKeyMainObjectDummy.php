<?php

declare(strict_types=1);

namespace App\Tests\Jira\Entity;

use App\Jira\Resources\IssueByKeyInterface;

class JiraIssueByKeyMainObjectDummy implements IssueByKeyInterface
{
    public function __construct(
        private readonly string $projectKey,
    ) {
    }

    public function getProjectKey(): string
    {
        return $this->projectKey;
    }
}
