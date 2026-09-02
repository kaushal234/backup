<?php

declare(strict_types=1);

namespace App\Message\Service;

class TechnicianOnCallJiraCommentSync
{
    public function __construct(
        private readonly string $commentIri,
    ) {
    }

    public function getCommentIri(): string
    {
        return $this->commentIri;
    }
}
