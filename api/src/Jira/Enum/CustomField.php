<?php

declare(strict_types=1);

namespace App\Jira\Enum;

enum CustomField: string
{
    case Sprint = 'customfield_10020';
    case TroubleTicketNumber = 'customfield_10105';
    case TroubleTicketLink = 'customfield_10106';
    case UserStoryNumber = 'customfield_10085';
}
