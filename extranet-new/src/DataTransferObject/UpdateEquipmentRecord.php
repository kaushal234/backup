<?php

declare(strict_types=1);

namespace App\DataTransferObject;

use App\DataTransferObject\TechnicianOnCall\Airport;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateEquipmentRecord
{
    public string $iri;

    public ?string $customerSerialNumber = null;

    #[Assert\NotNull(message: 'extranet.error.airport')]
    public ?Airport $airport = null;
}
