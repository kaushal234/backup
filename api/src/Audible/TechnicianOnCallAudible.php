<?php

declare(strict_types=1);

namespace App\Audible;

use App\Entity\Service\TechnicianOnCall;

class TechnicianOnCallAudible implements AudibleInterface
{
    public function supports(string $type): bool
    {
        return 'technician_on_call' === $type;
    }

    public function getClass(): string
    {
        return TechnicianOnCall::class;
    }

    public function getAudibleProperties(): array
    {
        return ['status', 'factoryFlag', 'unitOperationalStatus', 'originalSymptoms', 'originalRootCause', 'originalSolution'];
    }
}
