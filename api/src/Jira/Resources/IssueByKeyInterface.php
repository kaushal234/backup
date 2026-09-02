<?php

declare(strict_types=1);

namespace App\Jira\Resources;

interface IssueByKeyInterface
{
    public function getProjectKey(): string;
}
