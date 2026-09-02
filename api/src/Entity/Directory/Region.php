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
use App\Validator\Constraints\LockedValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['region', 'subdivision:light', 'people_public', 'business_unit_public', 'expose_legacy']]),
        new Post(security: "is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"),
        new Delete(security: "is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"),
    ],
    normalizationContext: ['groups' => ['region:detail', 'subdivision:light', 'people_public', 'business_unit_public', 'expose_legacy']],
    denormalizationContext: ['groups' => ['region:write']],
)]
#[ORM\Table(name: 'directory_region')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['legacyId' => 'exact', 'subDivision', 'subDivision.division'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id',
    'name' => 'partial',
])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'tld_regions')]
#[LockedValue(value: Region::INTEGRATED_THIRD_PARTIES, propertyPath: 'name')]
#[LockedValue(value: Region::TLD, propertyPath: 'name')]
#[LockedValue(value: Region::ALVEST, propertyPath: 'name')]
#[LockedValue(value: Region::GSE, propertyPath: 'name')]
class Region implements \Stringable, LegacyIdInterface
{
    use LegacyIdentifierTrait;
    final public const INTEGRATED_THIRD_PARTIES = 'INTEGRATED THIRD PARTIES';
    final public const TLD = 'TLD';
    final public const ALVEST = 'ALVEST';
    final public const GSE = 'GSE';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['region', 'region:detail'])]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(length: 100, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(['region', 'region_light', 'region:detail', 'region:write', 'people', 'division:tree', 'region:list', 'map_premise_people'])]
    #[Legacy\Column(column: 'division')]
    private string $name;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['region', 'region:detail', 'region:write'])]
    #[Transferable]
    #[Legacy\Column(column: 'repid', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?People $representative = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\SubDivision', inversedBy: 'regions')]
    #[Assert\NotNull]
    #[Groups(['region', 'region:detail', 'region:write', 'people:division', 'map_premise_people'])]
    #[Legacy\Column(column: 'sub_division_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?SubDivision $subDivision = null;

    /**
     * @var Collection<BusinessUnit>
     */
    #[ORM\OneToMany(mappedBy: 'region', targetEntity: 'App\Entity\Directory\BusinessUnit')]
    #[Groups(['region', 'region:detail', 'division:tree'])]
    private Collection $businessUnits;

    public function __construct()
    {
        $this->businessUnits = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return People
     */
    public function getRepresentative()
    {
        return $this->representative;
    }

    /**
     * @param People $representative
     *
     * @return $this
     */
    public function setRepresentative($representative)
    {
        $this->representative = $representative;

        return $this;
    }

    public function getSubDivision(): ?SubDivision
    {
        return $this->subDivision;
    }

    public function setSubDivision(SubDivision $subDivision): self
    {
        $this->subDivision = $subDivision;

        return $this;
    }

    /**
     * @return Collection<BusinessUnit>
     */
    public function getBusinessUnits(): Collection
    {
        return $this->businessUnits;
    }

    public function addBusinessUnit(BusinessUnit $businessUnit): self
    {
        $this->businessUnits->add($businessUnit);
        $businessUnit->setRegion($this);

        return $this;
    }

    public function removeBusinessUnit(BusinessUnit $businessUnit): self
    {
        $this->businessUnits->removeElement($businessUnit);
        $businessUnit->setRegion(null);

        return $this;
    }
}
