<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Filter\SimpleSearchFilter;
use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use App\Validator\Constraints\LockedValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Business Unit.
 */
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['business_unit', 'region_light', 'expose_legacy']]),
        new Post(security: "is_granted('FEATURE_BUSINESS_UNIT_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_BUSINESS_UNIT_WRITE')"),
        new Delete(security: "is_granted('FEATURE_BUSINESS_UNIT_WRITE')"),
    ],
    normalizationContext: ['groups' => ['region_light', 'business_unit_detail', 'people_public', 'expose_legacy']],
    denormalizationContext: ['groups' => ['business_unit_write']],
)]
#[UniqueEntity(fields: ['name'])]
#[ORM\Table(name: 'directory_businessunit')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: [
    'location.legacyId',
    'location',
    'region',
    'region.subDivision',
    'region.subDivision.division',
])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'name' => 'partial',
    'region.name' => 'partial',
])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'locations', forceUpdate: true)]
#[LockedValue(value: BusinessUnit::AEROSPECIALTIES, propertyPath: 'name')]
#[LockedValue(value: BusinessUnit::AERO_SSO, propertyPath: 'name')]
#[LockedValue(value: BusinessUnit::AIR_RAIL, propertyPath: 'name')]
#[LockedValue(value: BusinessUnit::AVPM, propertyPath: 'name')]
#[LockedValue(value: BusinessUnit::EASYMILE, propertyPath: 'name')]
#[LockedValue(value: BusinessUnit::GESFA, propertyPath: 'name')]
#[LockedValue(value: BusinessUnit::GILON_SUPPLY, propertyPath: 'name')]
#[LockedValue(value: BusinessUnit::ALVEST_ARABIA_EQUIPMENT_SERVICES, propertyPath: 'name')]
class BusinessUnit implements \Stringable, LegacyIdInterface
{
    /** @var string */
    public const AEROSPECIALTIES = 'AEROSPECIALTIES';
    /** @var string */
    public const AERO_SSO = 'AERO SSO';
    /** @var string */
    public const AIR_RAIL = 'AIR RAIL';
    /** @var string */
    public const AVPM = 'AVPM';
    /** @var string */
    public const EASYMILE = 'EASYMILE';
    /** @var string */
    public const GESFA = 'GESFA';
    /** @var string */
    public const GILON_SUPPLY = 'GILON SUPPLY';
    /** @var string */
    public const ALVEST_ARABIA_EQUIPMENT_SERVICES = 'ALVEST ARABIA EQUIPMENT SERVICES';
    /** @var string */
    public const AMAL = 'AMAL';
    /** @var string */
    public const AGSA = 'AGSA';
    /** @var string */
    public const SAGE = 'SAGE';

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['division:tree', 'business_unit_write', 'business_unit_detail'])]
    public bool $disabled = false;

    #[ORM\Column(name: 'legacy_id', type: 'integer', nullable: true)]
    #[Legacy\Id]
    #[Groups(['expose_legacy'])]
    protected ?int $legacyId = null;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['business_unit', 'business_unit_detail', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/Text'])]
    #[ORM\Column(length: 60, unique: true, nullable: false)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 60)]
    #[Assert\NotBlank]
    #[Groups(['business_unit', 'business_unit_detail', 'business_unit_public', 'business_unit_write', 'people_detail', 'location_detail', 'group_member', 'user:me', 'training_attendee:reports', 'division:tree', 'people:export', 'map_premise_people', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    #[Legacy\Column(column: 'business_unit')]
    private string $name;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Groups(['business_unit', 'business_unit_detail', 'business_unit_write', 'people_detail', 'location_detail', 'user:me'])]
    private ?string $domain = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['business_unit_detail', 'business_unit_write'])]
    #[Transferable]
    #[Legacy\Column(column: 'repid', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?People $representative = null;

    #[ORM\OneToOne(targetEntity: 'App\Entity\Directory\Location', inversedBy: 'businessUnit')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['business_unit_detail', 'business_unit_write', 'people_detail', 'user:me', 'location_public'])]
    private Location $location;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Region', inversedBy: 'businessUnits')]
    #[Assert\NotNull]
    #[Groups(['business_unit', 'business_unit_detail', 'business_unit_write', 'people:division', 'region:list', 'map_premise_people'])]
    #[Legacy\Column(column: 'region_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?Region $region = null;

    /**
     * @var Collection<PositionClassification>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Directory\PositionClassification', mappedBy: 'businessUnit')]
    private Collection $positionClassifications;

    public function __construct()
    {
        $this->positionClassifications = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getLegacyId(): ?int
    {
        return $this->location->getLegacyId() ?? 0;
    }

    public function setLegacyId(?int $id)
    {
        $this->legacyId = $this->location->getLegacyId() ?? 0;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRepresentative(): ?People
    {
        return $this->representative;
    }

    public function setRepresentative(?People $representative): self
    {
        $this->representative = $representative;

        return $this;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): self
    {
        $this->legacyId = $location->getLegacyId();
        $this->location = $location;

        return $this;
    }

    public function setDomain(?string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }
}
