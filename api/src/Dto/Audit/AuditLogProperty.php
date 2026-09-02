<?php

declare(strict_types=1);

namespace App\Dto\Audit;

use App\Entity\Directory\People;
use Symfony\Component\Serializer\Attribute\Groups;

class AuditLogProperty
{
    #[Groups(['audit_log'])]
    public string $value;

    #[Groups(['audit_log'])]
    public ?float $time = null;

    #[Groups(['audit_log:month'])]
    public ?string $month = null;

    #[Groups(['audit_log:reference'])]
    public ?People $createdBy = null;

    #[Groups(['audit_log:reference'])]
    public ?\DateTime $createdAt = null;
}
