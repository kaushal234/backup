<?php

declare(strict_types=1);

namespace App\Entity\Sales\EquipmentShippingRecord;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\EquipmentRecord;
use App\Repository\Sales\EquipmentShippingRecordLineRepository;
use App\Validator\Constraints\Sales\EquipmentShippingRecord\EquipmentRecordMatchesCustomer;
use App\Validator\Constraints\Sales\EquipmentShippingRecord\EquipmentRecordNotInAnotherOpenEsr;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: EquipmentShippingRecordLineRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['equipment_shipping_record_line:detail', 'equipment_shipping_record_line', 'equipment_record', 'location_public', 'expose_legacy']]),
        new Post(security: "is_granted('FEATURE_ESR_WRITE')"),
        new Get(
            normalizationContext: ['groups' => ['equipment_shipping_record_line:detail', 'equipment_record']],
            security: "is_granted('FEATURE_ESR_WRITE')",
        ),
        new Put(
            security: "is_granted('FEATURE_ESR_WRITE')",
            securityPostDenormalize: "is_granted('EDIT_PICKUP_CONFIRMATION', object)",
        ),
        new Delete(security: "is_granted('FEATURE_ESR_WRITE')"),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['equipment_shipping_record_line:detail']],
    denormalizationContext: ['groups' => ['equipment_shipping_record_line:write']],
)]
#[Legacy\Synchronize(table: 'esrl')]
#[UniqueEntity(fields: ['equipmentRecord', 'equipmentShippingRecord'], message: 'This ER was already added to this ESR.')]
#[ApiFilter(SearchFilter::class, properties: ['equipmentRecord.manufacturerLocation' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['estimatedPickUpDate'])]
#[ApiFilter(OrderFilter::class, properties: ['estimatedPickUpDate', 'equipmentShippingRecord.id'])]
#[ApiFilter(ExistsFilter::class, properties: ['equipmentRecord.dateShipped'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups', 'overrideDefaultGroups' => false, 'whitelist' => ['odp:view', 'expose_legacy']])]
#[App\Loggable(owner: 'equipmentShippingRecord', ownerRelation: 'equipmentShippingRecordLines')]
#[EquipmentRecordMatchesCustomer]
#[EquipmentRecordNotInAnotherOpenEsr]
class EquipmentShippingRecordLine implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\ManyToOne(targetEntity: EquipmentRecord::class, inversedBy: 'equipmentShippingRecordLines')]
    #[Groups(['equipment_shipping_records', 'equipment_shipping_record_line:detail', 'equipment_shipping_record_line:write', 'equipment_shipping_records:write'])]
    #[Legacy\Column(column: 'erid', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public EquipmentRecord $equipmentRecord;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['equipment_shipping_records', 'equipment_shipping_record_line:detail', 'equipment_shipping_record_line:write', 'equipment_shipping_records:write'])]
    #[Legacy\Column(column: 'dt_shipped', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    public ?\DateTimeInterface $vesselLoadingDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['equipment_shipping_records', 'equipment_shipping_record_line:detail', 'equipment_shipping_record_line:write', 'equipment_shipping_records:write'])]
    #[Legacy\Column(column: 'dt_estimated', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    public ?\DateTimeInterface $estimatedArrivalDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['equipment_shipping_records', 'equipment_shipping_record_line:detail', 'equipment_shipping_record_line:write', 'equipment_shipping_records:write'])]
    #[Legacy\Column(column: 'dt_arrived', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    public ?\DateTimeInterface $actualArrivalDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['equipment_shipping_records', 'equipment_shipping_record_line:detail', 'equipment_shipping_record_line:write', 'equipment_shipping_records:write'])]
    #[Legacy\Column(column: 'dt_pick_up', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    public ?\DateTimeInterface $estimatedPickUpDate = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord', inversedBy: 'equipmentShippingRecordLines')]
    #[ORM\JoinColumn(nullable: false)]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Groups(['equipment_shipping_record_line:write', 'equipment_shipping_record_line'])]
    public EquipmentShippingRecord $equipmentShippingRecord;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['equipment_shipping_records', 'equipment_shipping_record_line:detail', 'equipment_shipping_record_line:write', 'equipment_shipping_records:write'])]
    public ?string $truckType = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['equipment_shipping_records', 'equipment_shipping_record_line:detail', 'equipment_shipping_record_line:write', 'equipment_shipping_records:write'])]
    public ?string $comment = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['equipment_shipping_record_line:detail', 'equipment_shipping_record_line:write', 'equipment_shipping_records:write'])]
    public bool $estimatedPickUpDateConfirmation = false;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['equipment_shipping_record_line:detail'])]
    private int $id;

    public function __toString(): string
    {
        return \sprintf('equipment_shipping_record_line: %d, legacyId: %d, equipment_record_serial_number: %d', $this->id, $this->legacyId, $this->equipmentRecord->getSerialNumber());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function areDatesDifferent(?\DateTime $comparedDate, string $property): bool
    {
        return ($comparedDate?->format('Y-m-d') ?? '') === ($this->{$property}?->format('Y-m-d') ?? '');
    }
}
