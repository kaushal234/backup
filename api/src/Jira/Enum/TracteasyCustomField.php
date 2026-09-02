<?php

declare(strict_types=1);

namespace App\Jira\Enum;

enum TracteasyCustomField: string
{
    case TechnicianOnCallId = 'customfield_10096'; // We send the intranet link to the TOC
    case VehicleId = 'customfield_10134'; // TOC's ER Serial Number
    case ServiceInterrupted = 'customfield_10063'; // TOC's UnitOperationalStatus
    case Product = 'customfield_10112'; // TOC's ER Product (EZTow/EZDolly)
}
