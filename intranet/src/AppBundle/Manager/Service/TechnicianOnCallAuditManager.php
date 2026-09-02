<?php

declare(strict_types=1);

namespace AppBundle\Manager\Service;

use ApiBundle\Model\ApiData;
use AppBundle\Manager\AuditLogManager;

class TechnicianOnCallAuditManager
{
    public const AUDIT_TIME_URL = 'audit_logs/time_by_reference';

    public function __construct(
        private readonly AuditLogManager $auditLogManager,
    ) {
    }

    public function factoryFlagAudit(array|ApiData $technicianOnCall): \DateInterval
    {
        return $this->auditLogManager->timeByReference('technician_on_call', 'factoryFlag', $technicianOnCall['id'], '1');
    }

    public function unitOperationalStatusAudit(array|ApiData $technicianOnCall): \DateInterval
    {
        return $this->auditLogManager->timeByReference('technician_on_call', 'unitOperationalStatus', $technicianOnCall['id'], 'NMC');
    }
}
