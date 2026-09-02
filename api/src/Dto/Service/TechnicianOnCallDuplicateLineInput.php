<?php

declare(strict_types=1);

namespace App\Dto\Service;

use App\Entity\Common\Airport;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Service\NestedTechnicianOnCallCustomerServiceRecord;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final class TechnicianOnCallDuplicateLineInput
{
    #[Groups(['toc:write', 'equipment_record:service'])]
    #[Assert\NotNull]
    public ?EquipmentRecord $equipmentRecord;

    #[Groups(['toc:write', 'equipment_record:service'])]
    #[Assert\NotNull]
    public ?Airport $airport;

    #[Groups(['toc:write', 'equipment_record:service'])]
    #[Assert\NotNull]
    public ?Location $salesOrganisationService;

    #[Groups(['toc:write', 'equipment_record:service'])]
    public ?int $hourMeter = null;

    #[Groups(['toc:write', 'equipment_record:service'])]
    public ?NestedTechnicianOnCallCustomerServiceRecord $nestedCustomerServiceRecord = null;
}
