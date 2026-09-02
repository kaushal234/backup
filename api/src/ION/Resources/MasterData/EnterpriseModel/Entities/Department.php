<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\EnterpriseModel\Entities;

use App\ION\Resources\MasterData\EnterpriseModel\Employee;
use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use Symfony\Component\Serializer\Attribute\Groups;

class Department
{
    #[Groups(['department'])]
    public string $code;

    #[Groups(['department', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public string $erp;

    #[Groups(['department'])]
    public string $name;

    #[Groups(['department', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public ?Employee $buyer;
}
