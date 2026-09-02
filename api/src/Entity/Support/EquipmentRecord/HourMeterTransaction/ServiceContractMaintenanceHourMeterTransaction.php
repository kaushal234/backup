<?php

declare(strict_types=1);

namespace App\Entity\Support\EquipmentRecord\HourMeterTransaction;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'scm_hour_meter_transactions')]
#[ApiResource(
    operations: [
        new Get(),
    ],
    routePrefix: 'support/equipment_record',
    normalizationContext: ['groups' => ['hour_meter_transaction']],
    denormalizationContext: []
)]
class ServiceContractMaintenanceHourMeterTransaction extends HourMeterTransaction
{
    #[Legacy\Column(column: 'module')]
    #[Groups(['hour_meter_transaction'])]
    protected string $module = 'SCM';

    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): self
    {
        $this->module = $module;

        return $this;
    }
}
