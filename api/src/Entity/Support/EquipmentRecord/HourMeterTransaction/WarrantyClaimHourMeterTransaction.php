<?php

declare(strict_types=1);

namespace App\Entity\Support\EquipmentRecord\HourMeterTransaction;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Validator\Constraints\HourMeterTransaction as HourMeterTransactionConstraint;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'wc_hour_meter_transactions')]
#[ApiResource(
    operations: [
        new Post(denormalizationContext: ['groups' => ['wc_hour_meter_transaction:create', 'hour_meter_transaction:create']]),
        new Get(),
        new Put(),
    ],
    routePrefix: 'support/equipment_record',
    normalizationContext: ['groups' => ['hour_meter_transaction', 'equipment_list']],
    denormalizationContext: ['groups' => ['hour_meter_transaction:edit']],
)]
#[Legacy\Synchronize(table: 'service_hourmeter')]
#[HourMeterTransactionConstraint]
class WarrantyClaimHourMeterTransaction extends HourMeterTransaction
{
    #[ORM\Column(type: 'integer')]
    #[Legacy\Column(column: 'module_id')]
    #[Groups(['hour_meter_transaction', 'wc_hour_meter_transaction:create'])]
    public int $warrantyClaimLegacyId;

    #[Legacy\Column(column: 'module')]
    #[Groups(['hour_meter_transaction', 'hour_meter_transaction:create'])]
    protected string $module = 'WC';

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
