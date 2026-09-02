<?php

declare(strict_types=1);

namespace App\DataTransferObject\TechnicianOnCall;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTechnicianOnCall
{
    #[Assert\NotBlank(message: 'extranet.error.equipment_record')]
    #[Assert\Valid]
    public ?Equipment $equipment = null;

    #[Assert\NotBlank(message: 'extranet.error.airport')]
    #[Assert\Valid]
    public ?Airport $airport = null;

    #[Assert\NotBlank(message: 'extranet.error.toc_title')]
    public ?string $originalTitle = null;

    #[Assert\NotBlank(message: 'extranet.error.description')]
    public ?string $originalDescription = null;

    public ?string $errorCodes = null;

    #[Assert\NotBlank(message: 'extranet.error.hour_meter')]
    public ?int $hourMeter = null;

    #[Assert\NotBlank(message: 'extranet.error.service_activity')]
    public ?string $serviceActivity = null;

    #[Assert\NotBlank(message: 'extranet.error.unit_operational_status')]
    public ?string $unitOperationalStatus = null;
}
