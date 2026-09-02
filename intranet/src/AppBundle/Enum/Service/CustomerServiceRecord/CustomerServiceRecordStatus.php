<?php

declare(strict_types=1);

namespace AppBundle\Enum\Service\CustomerServiceRecord;

enum CustomerServiceRecordStatus: string
{
    case COMPLETED = 'COMPLETED';
    case CLOSED = 'CLOSED';
}
