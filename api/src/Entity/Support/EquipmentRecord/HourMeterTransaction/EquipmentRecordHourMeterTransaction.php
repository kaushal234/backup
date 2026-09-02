<?php

declare(strict_types=1);

namespace App\Entity\Support\EquipmentRecord\HourMeterTransaction;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\EquipmentRecord;
use App\Validator\Constraints\HourMeterTransaction as HourMeterTransactionConstraint;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'er_hour_meter_transactions')]
#[ApiResource(
    operations: [
        new Post(denormalizationContext: ['groups' => ['er_hour_meter_transaction:create', 'hour_meter_transaction:create']]),
        new Get(),
        new Put(),
    ],
    routePrefix: 'support/equipment_record',
    normalizationContext: ['groups' => ['hour_meter_transaction', 'equipment_list']],
    denormalizationContext: ['groups' => ['hour_meter_transaction:edit']],
)]
#[Legacy\Synchronize(table: 'service_hourmeter')]
#[HourMeterTransactionConstraint]
class EquipmentRecordHourMeterTransaction extends HourMeterTransaction
{
    #[Legacy\Column(column: 'module')]
    #[Groups(['hour_meter_transaction', 'er_hour_meter_transaction:create'])]
    protected string $module = 'ER';

    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Legacy\Column(column: 'module_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Groups(['hour_meter_transaction', 'er_hour_meter_transaction:create'])]
    protected EquipmentRecord $equipmentRecord;

    public function getEquipmentRecord(): EquipmentRecord
    {
        return $this->equipmentRecord;
    }

    public function setEquipmentRecord(EquipmentRecord $equipmentRecord): self
    {
        $this->equipmentRecord = $equipmentRecord;

        return $this;
    }

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
