<?php

declare(strict_types=1);

namespace App\Entity\Finance;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\Finance\ManufacturingMarginBatchController;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\EquipmentRecord;
use App\Filter\ColumnsFilter;
use App\Serializer\Filter\ContextFilter;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['equipmentRecord'], message: 'Record for ER ({{ value }}) already exists')]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => ['manufacturing_margin', 'equipment_list', 'currency', 'expose_legacy']],
        ),
        new Post(security: "is_granted('FEATURE_MANUFACTURING_MARGIN_CREATE')"),
        new Post(
            uriTemplate: '/manufacturing_margins/import_file',
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            controller: ManufacturingMarginBatchController::class,
            security: "is_granted('FEATURE_MANUFACTURING_MARGIN_CREATE')",
            output: false,
            deserialize: false,
            validate: false,
            name: 'import_manufacturing_margin_file',
        ),
        new Get(security: "is_granted('MANUFACTURING_MARGIN_VIEW_VOTER', object)"),
        new Put(security: "is_granted('MANUFACTURING_MARGIN_EDIT_VOTER', object)"),
        new Delete(security: "is_granted('FEATURE_MANUFACTURING_MARGIN_DELETE')"),
    ],
    routePrefix: 'finance',
    normalizationContext: ['groups' => ['manufacturing_margin', 'manufacturing_margin:detail', 'equipment_list', 'currency', 'expose_legacy']],
    denormalizationContext: ['groups' => ['manufacturing_margin:write']],
)]
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'unique_equipment_record', columns: ['equipment_record_id'])]
#[ApiFilter(SearchFilter::class, properties: ['legacyId' => 'exact', 'equipmentRecord.manufacturerLocation' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['exportedAt'])]
#[ApiFilter(ContextFilter::class)]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalizationGroupsOverride', 'overrideDefaultGroups' => true, 'whitelist' => ['manufacturing_margin_export_by_er', 'manufacturing_margin_export_by_er_full']])]
#[ApiFilter(ColumnsFilter::class)]
#[App\Loggable]
#[Legacy\Synchronize(table: 'mfg_margins')]
class ManufacturingMargin
{
    use LegacyIdentifierTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['manufacturing_margin'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\EquipmentRecord')]
    #[Legacy\Column(column: 'er_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Groups(['manufacturing_margin', 'manufacturing_margin:write'])]
    private ?EquipmentRecord $equipmentRecord = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull]
    #[Groups(['manufacturing_margin', 'manufacturing_margin:write'])]
    #[Legacy\Column(column: 'year', transformer: DateTimeToString::class, options: ['format' => 'Y', 'integer' => true])]
    #[Legacy\Column(column: 'month', transformer: DateTimeToString::class, options: ['format' => 'n', 'integer' => true])]
    private \DateTimeInterface $exportedAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Finance\Currency')]
    #[ORM\JoinColumn(nullable: false)]
    #[Legacy\Column(column: 'cur', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    #[Assert\NotNull]
    #[Groups(['manufacturing_margin:detail', 'manufacturing_margin:write'])]
    private Currency $currency;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Assert\NotNull]
    #[Groups(['manufacturing_margin:detail', 'manufacturing_margin:write'])]
    #[Legacy\Column(column: 'ocp_hours')]
    private float $optionConfigurationParameterHours = 0.0;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups(['manufacturing_margin:detail', 'manufacturing_margin:write'])]
    #[Legacy\Column(column: 'act_hour')]
    private float $actualHours = 0.0;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups(['manufacturing_margin:detail', 'manufacturing_margin:write'])]
    #[Legacy\Column(column: 'std_hour')]
    private float $standardHours = 0.0;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups(['manufacturing_margin:detail', 'manufacturing_margin:write'])]
    #[Legacy\Column(column: 'std_lab_cost')]
    private float $standardLabourCost = 0.0;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups(['manufacturing_margin:detail', 'manufacturing_margin:write'])]
    #[Legacy\Column(column: 'act_lab_cost')]
    private float $actualLabourCost = 0.0;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups(['manufacturing_margin:detail', 'manufacturing_margin:write'])]
    #[Legacy\Column(column: 'std_mat')]
    private float $standardMaterialCost = 0.0;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups(['manufacturing_margin:detail', 'manufacturing_margin:write'])]
    #[Legacy\Column(column: 'act_mat')]
    private float $actualMaterialCost = 0.0;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups(['manufacturing_margin:detail', 'manufacturing_margin:write'])]
    #[Legacy\Column(column: 'std_other_mat')]
    private float $standardOtherMaterialCost = 0.0;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups(['manufacturing_margin:detail', 'manufacturing_margin:write'])]
    #[Legacy\Column(column: 'act_other_mat')]
    private float $actualOtherMaterialCost = 0.0;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups(['manufacturing_margin:detail', 'manufacturing_margin:write'])]
    #[Legacy\Column(column: 'std_other_dir_cost')]
    private float $standardOtherDirectCost = 0.0;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups(['manufacturing_margin:detail', 'manufacturing_margin:write'])]
    #[Legacy\Column(column: 'act_other_dir_cost')]
    private float $actualOtherDirectCost = 0.0;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups(['manufacturing_margin:detail', 'manufacturing_margin:write'])]
    #[Legacy\Column(column: 'factory_rev')]
    private float $factoryRevenue = 0.0;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['manufacturing_margin:detail', 'manufacturing_margin:write'])]
    #[Legacy\Column(column: 'comment')]
    private ?string $comment = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getEquipmentRecord(): ?EquipmentRecord
    {
        return $this->equipmentRecord;
    }

    public function setEquipmentRecord(?EquipmentRecord $equipmentRecord): self
    {
        $this->equipmentRecord = $equipmentRecord;

        return $this;
    }

    public function getExportedAt(): \DateTimeInterface
    {
        return $this->exportedAt;
    }

    public function setExportedAt(\DateTime $exportedAt): self
    {
        $this->exportedAt = $exportedAt;

        return $this;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function setCurrency($currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getOptionConfigurationParameterHours(): ?float
    {
        return $this->optionConfigurationParameterHours;
    }

    public function setOptionConfigurationParameterHours(float $optionConfigurationParameterHours): self
    {
        $this->optionConfigurationParameterHours = $optionConfigurationParameterHours;

        return $this;
    }

    public function getActualHours(): float
    {
        return $this->actualHours;
    }

    public function setActualHours(float $actualHours): self
    {
        $this->actualHours = $actualHours;

        return $this;
    }

    public function getStandardHours(): float
    {
        return $this->standardHours;
    }

    public function setStandardHours(float $standardHours): self
    {
        $this->standardHours = $standardHours;

        return $this;
    }

    public function getStandardLabourCost(): float
    {
        return $this->standardLabourCost;
    }

    public function setStandardLabourCost(float $standardLabourCost): self
    {
        $this->standardLabourCost = $standardLabourCost;

        return $this;
    }

    public function getActualLabourCost(): float
    {
        return $this->actualLabourCost;
    }

    public function setActualLabourCost(float $actualLabourCost): self
    {
        $this->actualLabourCost = $actualLabourCost;

        return $this;
    }

    public function getStandardMaterialCost(): float
    {
        return $this->standardMaterialCost;
    }

    public function setStandardMaterialCost(float $standardMaterialCost): self
    {
        $this->standardMaterialCost = $standardMaterialCost;

        return $this;
    }

    public function getActualMaterialCost(): float
    {
        return $this->actualMaterialCost;
    }

    public function setActualMaterialCost(float $actualMaterialCost): self
    {
        $this->actualMaterialCost = $actualMaterialCost;

        return $this;
    }

    public function getStandardOtherMaterialCost(): float
    {
        return $this->standardOtherMaterialCost;
    }

    public function setStandardOtherMaterialCost(float $standardOtherMaterialCost): self
    {
        $this->standardOtherMaterialCost = $standardOtherMaterialCost;

        return $this;
    }

    public function getActualOtherMaterialCost(): float
    {
        return $this->actualOtherMaterialCost;
    }

    public function setActualOtherMaterialCost(float $actualOtherMaterialCost): self
    {
        $this->actualOtherMaterialCost = $actualOtherMaterialCost;

        return $this;
    }

    public function getStandardOtherDirectCost(): float
    {
        return $this->standardOtherDirectCost;
    }

    public function setStandardOtherDirectCost(float $standardOtherDirectCost): self
    {
        $this->standardOtherDirectCost = $standardOtherDirectCost;

        return $this;
    }

    public function getActualOtherDirectCost(): float
    {
        return $this->actualOtherDirectCost;
    }

    public function setActualOtherDirectCost(float $actualOtherDirectCost): self
    {
        $this->actualOtherDirectCost = $actualOtherDirectCost;

        return $this;
    }

    public function getFactoryRevenue(): float
    {
        return $this->factoryRevenue;
    }

    public function setFactoryRevenue(float $factoryRevenue): self
    {
        $this->factoryRevenue = $factoryRevenue;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getActualDirectMargin(): float
    {
        return round($this->getFactoryRevenue() - $this->getActualLabourCost() - $this->getActualMaterialCost() - $this->getActualOtherMaterialCost() - $this->getActualOtherDirectCost(), 2);
    }

    public function getActualDirectMarginPercentage(): ?float
    {
        if (0.0 === $this->getFactoryRevenue()) {
            return null;
        }

        return round(($this->getActualDirectMargin() / $this->getFactoryRevenue()) * 100, 2);
    }

    public function getStandardDirectMarginPercentage(): float
    {
        return round((($this->getFactoryRevenue() - $this->getStandardLabourCost() - $this->getStandardMaterialCost() - $this->getStandardOtherMaterialCost() - $this->getStandardOtherDirectCost()) / $this->getFactoryRevenue()) * 100, 2);
    }
}
