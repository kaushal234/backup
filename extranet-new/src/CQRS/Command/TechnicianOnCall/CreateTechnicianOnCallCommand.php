<?php

declare(strict_types=1);

namespace App\CQRS\Command\TechnicianOnCall;

use App\CQRS\Command\CommandInterface;

class CreateTechnicianOnCallCommand implements CommandInterface
{
    public function __construct(
        public readonly string $originalTitle,
        public readonly string $originalDescription,
        public readonly string $serviceActivity,
        public readonly string $unitOperationalStatus,
        public readonly string $mainContact,
        public readonly int $hourMeter,
        public readonly string $airport,
        public readonly string $equipmentRecord,
        public readonly ?string $errorCodes = null,
        public readonly ?string $technicianOnCallType = null,
    ) {
    }
}
