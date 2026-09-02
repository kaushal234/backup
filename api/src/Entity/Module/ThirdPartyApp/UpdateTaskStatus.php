<?php

declare(strict_types=1);

namespace App\Entity\Module\ThirdPartyApp;

enum UpdateTaskStatus: string
{
    case InProgress = 'IN_PROGRESS';
    case Confirmed = 'CONFIRMED';
    case Denied = 'DENIED';
}
