<?php

declare(strict_types=1);

namespace App\Entity\Support\EquipmentRecord\HourMeterTransaction;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\EquipmentRecord;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap([
    'er_hour_meter' => EquipmentRecordHourMeterTransaction::class,
    'toc_hour_meter' => TechnicianOnCallHourMeterTransaction::class,
    'customer_service_record_hour_meter' => CustomerServiceRecordHourMeterTransaction::class,
    'warranty_claim_hour_meter' => WarrantyClaimHourMeterTransaction::class,
    'service_contract_maintenance_hour_meter' => ServiceContractMaintenanceHourMeterTransaction::class,
])]
#[ORM\Table(name: 'hour_meter_transactions')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'support/equipment_record',
    normalizationContext: ['groups' => ['hour_meter_transaction:light', 'hour_meter_transaction', 'equipment_list']],
    denormalizationContext: []
)]
#[ApiFilter(OrderFilter::class, properties: ['id'])]
#[Legacy\Synchronize(table: 'service_hourmeter')]

abstract class HourMeterTransaction implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Column(type: 'integer')]
    #[Legacy\Column(column: 'hourmeter')]
    #[Groups(['hour_meter_transaction:light', 'hour_meter_transaction', 'hour_meter_transaction:create', 'hour_meter_transaction:edit'])]
    public int $hourMeter;

    #[ORM\Column(type: 'datetime')]
    #[Timestampable(on: 'create')]
    #[Legacy\Column(column: 'dt', transformer: DateTimeToString::class)]
    #[Groups(['hour_meter_transaction', 'hour_meter_transaction:create'])]
    public \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string')]
    #[Legacy\Column(column: 'module')]
    #[Groups(['hour_meter_transaction', 'hour_meter_transaction:create'])]
    protected string $module;

    #[ORM\ManyToOne(targetEntity: EquipmentRecord::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Groups(['hour_meter_transaction', 'hour_meter_transaction:create'])]
    protected EquipmentRecord $equipmentRecord;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['hour_meter_transaction'])]

    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

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
