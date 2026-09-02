<?php

declare(strict_types=1);

namespace App\Factory\Service;

use App\Entity\Service\TechnicianOnCall;
use App\Jira\Resources\TracteasyHelpdeskIssue;
use App\Jira\Resources\TracteasyIssueType;

class TracteasyHelpdeskIssueFactory
{
    public function create(TechnicianOnCall $technicianOnCall, TracteasyIssueType $issueType): TracteasyHelpdeskIssue
    {
        $issue = new TracteasyHelpdeskIssue();
        $issue->type = $issueType;
        $issue->summary = \sprintf(
            '[%s] %s — %s',
            $technicianOnCall->equipmentRecord->getSerialNumber(),
            $technicianOnCall->customer->getName(),
            $technicianOnCall->title
        );
        $issue->description = $technicianOnCall->description;
        $issue->technicianOnCallId = (string) $technicianOnCall->getId();
        $issue->vehicleId = $technicianOnCall->equipmentRecord->getSerialNumber();
        $issue->serviceInterrupted = $technicianOnCall->unitOperationalStatus;
        $issue->product = $technicianOnCall->equipmentRecord->getProduct()?->getName();

        return $issue;
    }
}
