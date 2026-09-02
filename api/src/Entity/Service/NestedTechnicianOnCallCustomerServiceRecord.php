<?php

declare(strict_types=1);

namespace App\Entity\Service;

use App\Entity\Directory\People;
use Symfony\Component\Serializer\Attribute\Groups;

class NestedTechnicianOnCallCustomerServiceRecord
{
    #[Groups(['toc:write', 'equipment_record:service', 'toc:request_technician'])]
    public ?People $leader = null;

    #[Groups(['toc:write', 'equipment_record:service', 'toc:request_technician'])]
    public ?\DateTime $plannedAt = null;
}
