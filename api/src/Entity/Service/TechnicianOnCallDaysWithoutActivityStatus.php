<?php

declare(strict_types=1);

namespace App\Entity\Service;

enum TechnicianOnCallDaysWithoutActivityStatus
{
    case ON_TIME;
    case OUTDATED;
}
