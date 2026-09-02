<?php

declare(strict_types=1);

namespace App\Entity\Sales\EquipmentShippingRecord;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Directory\People;
use App\Entity\Finance\Currency;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(security: "is_granted('FEATURE_ESR_WRITE')"),
        new Get(
            normalizationContext: ['groups' => ['equipment_shipping_record_cost:detail']],
            security: "is_granted('FEATURE_ESR_WRITE')",
        ),
        new Put(security: "is_granted('FEATURE_ESR_WRITE')"),
        new Delete(security: "is_granted('FEATURE_ESR_WRITE')"),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['equipment_shipping_record_cost:detail']],
    denormalizationContext: ['groups' => ['equipment_shipping_record_cost:write']],
)]
#[Legacy\Synchronize(table: 'mod_costs')]
#[ORM\Entity]
class EquipmentShippingRecordCost implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    public const TRANSPORT = 'Transport';
    public const LOADING_UNLOADING = 'Loading / Unloading';
    public const CUSTOMS_DUTIES = 'Customs duties';
    public const OTHER = 'Others';

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['equipment_shipping_record_cost:detail', 'equipment_shipping_record_cost:write'])]
    #[Gedmo\Blameable(on: 'create')]
    #[Legacy\Column(column: 'uid', transformer: ObjectToProperty::class, options: ['property' => 'legacyID'])]
    #[Legacy\Column(column: 'poster', transformer: ObjectToProperty::class, options: ['property' => 'legacyID'])]
    public People $createdBy;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['equipment_shipping_record_cost:detail', 'equipment_shipping_record_cost:write'])]
    #[Legacy\Column(column: 'date', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    public \DateTimeInterface $costDate;

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: [self::TRANSPORT, self::LOADING_UNLOADING, self::CUSTOMS_DUTIES, self::OTHER])]
    #[Groups(['equipment_shipping_record_cost:detail', 'equipment_shipping_record_cost:write'])]
    #[Legacy\Column(column: 'type')]
    public string $type = self::TRANSPORT;

    #[ORM\Column(type: 'string')]
    #[Groups(['equipment_shipping_record_cost:detail', 'equipment_shipping_record_cost:write'])]
    #[Legacy\Column(column: 'description')]
    public string $description;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Finance\Currency')]
    #[Groups(['equipment_shipping_record_cost:detail', 'equipment_shipping_record_cost:write'])]
    #[Legacy\Column(column: 'cur', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    public Currency $currency;

    #[ORM\Column(type: 'float')]
    #[Groups(['equipment_shipping_record_cost:detail', 'equipment_shipping_record_cost:write'])]
    #[Legacy\Column(column: 'price')]
    public float $price;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord', inversedBy: 'equipmentShippingRecordCosts')]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Groups(['equipment_shipping_record_cost:write', 'equipment_shipping_record_cost'])]
    public EquipmentShippingRecord $equipmentShippingRecord;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['equipment_shipping_record_cost:detail', 'equipment_shipping_record_cost:write'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
