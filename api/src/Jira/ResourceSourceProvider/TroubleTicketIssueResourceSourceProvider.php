<?php

declare(strict_types=1);

namespace App\Jira\ResourceSourceProvider;

use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Jira\Resources\TroubleTicketIssue;

class TroubleTicketIssueResourceSourceProvider extends IssueResourceSourceProvider
{
    public function getMainClass(): ?string
    {
        return TroubleTicket::class;
    }

    public function supports(string $class): bool
    {
        return TroubleTicketIssue::class === $class;
    }
}
