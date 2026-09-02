<?php

declare(strict_types=1);

namespace App\Jira\Resources;

interface IssueInterface
{
    public function getProjectNumber(): int;
}
