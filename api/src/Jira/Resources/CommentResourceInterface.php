<?php

declare(strict_types=1);

namespace App\Jira\Resources;

interface CommentResourceInterface
{
    public function getIssueKey(): string;
}
