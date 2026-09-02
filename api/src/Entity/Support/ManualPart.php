<?php

declare(strict_types=1);

namespace App\Entity\Support;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\BooleanToChar;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Doctrine\Transformer\ToString;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            openapi: true,
            normalizationContext: ['groups' => ['manual_part:recommended_spare_part_list', 'expose_legacy']],
        ),
        new Get(
            openapi: true,
        ),
    ],
    routePrefix: 'support',
    denormalizationContext: ['groups' => ['manual_part:write']]
)]
#[ORM\Entity]
#[ORM\Table(name: 'manual_parts')]
#[ORM\Index(columns: ['part_number'])]
#[ORM\Index(columns: ['description', 'other_description'], name: 'idx_fulltext_desc_parts', flags: ['fulltext'])]
#[ApiFilter(SearchFilter::class, properties: ['document.manual' => 'exact', 'preventive' => 'exact', 'maintenance' => 'exact', 'overhaul' => 'exact', 'critical' => 'exact'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['preventive' => 'exact', 'maintenance' => 'exact', 'overhaul' => 'exact', 'critical' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['document.category.name', 'document.id', 'partNumber', 'position'])]
#[Legacy\Synchronize(table: 'manuals_parts')]
class ManualPart implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Support\ManualDocument', inversedBy: 'parts')]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Groups(['manual_part:recommended_spare_part_list'])]
    #[MaxDepth(1)]
    public ManualDocument $document;

    #[ORM\Column(type: 'integer')]
    #[Assert\GreaterThanOrEqual(value: 0)]
    #[Assert\NotNull]
    #[Assert\NotBlank(groups: [Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    #[Groups(['manual_part', 'manual_part:recommended_spare_part_list', 'manual_part:write'])]
    #[Legacy\Column(column: 'item', transformer: ToString::class)]
    public int $position;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank(groups: [Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    #[Groups(['manual_part', 'manual_part:recommended_spare_part_list', 'manual_part:write'])]
    #[Legacy\Column(column: 'pn', transformer: Utf8ToHtmlEntities::class)]
    public string $partNumber;

    #[ORM\Column(type: 'float', length: 20, nullable: true)]
    #[Assert\GreaterThanOrEqual(0, groups: [Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    #[Groups(['manual_part', 'manual_part:recommended_spare_part_list', 'manual_part:write'])]
    #[Legacy\Column(column: 'qty', transformer: Utf8ToHtmlEntities::class)]
    public ?float $quantity = 0.0;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    #[Groups(['manual_part', 'manual_part:write'])]
    #[Legacy\Column(column: 'um', transformer: Utf8ToHtmlEntities::class)]
    public ?string $unitOfMeasure = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Groups(['manual_part', 'manual_part:recommended_spare_part_list', 'manual_part:write'])]
    #[Legacy\Column(column: 'en', transformer: Utf8ToHtmlEntities::class)]
    public ?string $description = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Groups(['manual_part', 'manual_part:recommended_spare_part_list', 'manual_part:write'])]
    #[Legacy\Column(column: 'fr', transformer: Utf8ToHtmlEntities::class)]
    public ?string $otherDescription = null;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(groups: [Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    #[Assert\Type(type: 'boolean', groups: [Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    #[Groups(['manual_part', 'manual_part:recommended_spare_part_list', 'manual_part:write'])]
    #[Legacy\Column(column: 'group_p', transformer: BooleanToChar::class, options: ['trueValue' => 'P', 'falseValue' => false])]
    public bool $preventive = false;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(groups: [Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    #[Assert\Type(type: 'boolean', groups: [Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    #[Groups(['manual_part', 'manual_part:recommended_spare_part_list', 'manual_part:write'])]
    #[Legacy\Column(column: 'group_m', transformer: BooleanToChar::class, options: ['trueValue' => 'M', 'falseValue' => false])]
    public bool $maintenance = false;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(groups: [Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    #[Assert\Type(type: 'boolean', groups: [Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    #[Groups(['manual_part', 'manual_part:recommended_spare_part_list', 'manual_part:write'])]
    #[Legacy\Column(column: 'group_o', transformer: BooleanToChar::class, options: ['trueValue' => 'O', 'falseValue' => false])]
    public bool $overhaul = false;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(groups: [Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    #[Assert\Type(type: 'boolean', groups: [Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    #[Groups(['manual_part', 'manual_part:recommended_spare_part_list', 'manual_part:write'])]
    #[Legacy\Column(column: 'group_c', transformer: BooleanToChar::class, options: ['trueValue' => 'C', 'falseValue' => false])]
    public bool $critical = false;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['manual_part', 'manual_part:recommended_spare_part_list'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
