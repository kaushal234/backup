<?php

declare(strict_types=1);

namespace App\Message\Service;

class TechnicianOnCallJiraTracteasyIssueCreate
{
    public function __construct(
        private readonly string $technicianOnCallIri,
        private readonly string $userIri,
    ) {
    }

    public function getTechnicianOnCallIri(): string
    {
        return $this->technicianOnCallIri;
    }

    public function getUserIri(): string
    {
        return $this->userIri;
    }
}
