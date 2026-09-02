<?php

declare(strict_types=1);

namespace AppBundle\Enum\Service\CustomerServiceRecord;

enum InterventionStatus: string
{
    case PENDING = 'PENDING';
    case STARTED = 'STARTED';
    case TO_CONTINUE = 'TO_CONTINUE';
    case SOLVED = 'SOLVED';
}
