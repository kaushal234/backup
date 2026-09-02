<?php

declare(strict_types=1);

namespace App\Manager\Service;

use App\Entity\Service\TechnicianOnCall;

class TechnicianOnCallManager
{
    public function getTechnicianEmail(TechnicianOnCall $technicianOnCall): ?string
    {
        if ($technicianOnCall->getCustomerServiceRecords()->isEmpty() || !$technicianOnCall->hasOpenCustomerServiceRecords()) {
            return null;
        }

        return $technicianOnCall->getCurrentCustomerServiceRecord()->leader?->getEmail();
    }
}
