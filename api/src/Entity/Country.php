<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Entity\Sales\SalesArea;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use libphonenumber\CountryCodeToRegionCodeMap;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Country.
 */
#[ORM\Entity(repositoryClass: 'App\Repository\CountryRepository')]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['country', 'expose_legacy', 'continent']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
        ),
        new Post(security: "is_granted('FEATURE_COUNTRY_WRITE')"),
        new Get(
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
        ),
        new Put(security: "is_granted('FEATURE_COUNTRY_WRITE') or is_granted('FEATURE_COUNTRY_ASM_WRITE')"),
    ],
    normalizationContext: ['groups' => ['country_detail', 'expose_legacy', 'people_public', 'continent', 'extranet_user_address_campaign']],
    denormalizationContext: ['groups' => []],
)]
#[UniqueEntity(fields: ['name', 'isoCode2'])]
#[ORM\Table(name: 'countries')]
#[ORM\Index(columns: ['name', 'iso_code_2'])]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'isoCode2' => 'exact', 'legacyId' => 'exact', 'region' => 'exact', 'name' => 'partial', 'continent' => 'exact'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
#[ApiFilter(BooleanFilter::class, properties: ['public'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups', 'overrideDefaultGroups' => false, 'whitelist' => ['people_public', 'sales_area_public', 'network']])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['country_list', 'country_phone_code']])]
#[Legacy\Synchronize(table: 'countries')]
class Country implements \Stringable, LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['country', 'country_detail', 'country_list', 'iata_code_detail', 'iata_code'])]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string', length: 100, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[Groups(['country', 'country_detail', 'country_write', 'iata_code', 'iata_code_detail', 'customer_export', 'country_list', 'sfr_export', 'customer_public', 'country_phone_code', 'odp:view', 'extranet_user_address_campaign'])]
    #[Legacy\Column(column: 'name')]
    private ?string $name = null;

    #[ORM\Column(name: 'alternate_names', type: 'text')]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[ApiProperty(iris: ['https://schema.org/alternateName'])]
    #[Groups(['country_detail', 'country_write'])]
    #[Legacy\Column(column: 'alt_name')]
    private string $alternateNames;

    #[ORM\Column(name: 'iso_code_2', type: 'string', length: 2, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 2)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['country', 'country_detail', 'country_write', 'iata_code_detail', 'country_list'])]
    #[Legacy\Column(column: 'iso_code_2')]
    private string $isoCode2;

    #[ORM\Column(name: 'iso_code_3', type: 'string', length: 3, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 3)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['country_detail', 'country_write'])]
    #[Legacy\Column(column: 'iso_code_3')]
    private string $isoCode3;

    #[ORM\Column(name: 'nb_code', type: 'integer', unique: true)]
    #[Assert\Type(type: 'integer')]
    #[ApiProperty(iris: ['https://schema.org/Integer'])]
    #[Groups(['country_detail', 'country_write'])]
    #[Legacy\Column(column: 'nb_code')]
    private int $nbCode;

    #[ORM\Column(name: 'fips_code', type: 'string', length: 10, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 10)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['country_detail', 'country_write'])]
    #[Legacy\Column(column: 'fips_code')]
    private ?string $fipsCode = null;

    #[ORM\Column(name: 'fips_name', type: 'string', length: 100, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 100)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['country_detail', 'country_write'])]
    #[Legacy\Column(column: 'fips_name')]
    private ?string $fipsName = null;

    #[ORM\Column(name: 'region', type: 'string', length: 100)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['country', 'country_detail', 'country_write'])]
    #[Legacy\Column(column: 'region')]
    private string $region;

    #[ORM\Column(name: 'sub_region', type: 'string', length: 100)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['country', 'country_detail', 'country_write'])]
    #[Legacy\Column(column: 'sub_region')]
    private string $subRegion;

    #[ORM\Column(name: 'latitude', type: 'string', length: 20, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 20)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['country_detail', 'country_write'])]
    #[Legacy\Column(column: 'gps_lat')]
    private ?string $latitude = null;

    #[ORM\Column(name: 'longitude', type: 'string', length: 20, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 20)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['country_detail', 'country_write'])]
    #[Legacy\Column(column: 'gps_long')]
    private ?string $longitude = null;

    /**
     * @var Collection<SalesArea>
     */
    #[ORM\OneToMany(mappedBy: 'country', targetEntity: 'App\Entity\Sales\SalesArea')]
    #[Groups(['sales_area_public'])]
    private Collection $salesAreas;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Continent', inversedBy: 'countries')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['country_detail', 'country'])]
    private ?Continent $continent = null;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['country_detail'])]
    private bool $public = true;

    public function __construct()
    {
        $this->salesAreas = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Country
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set alternateNames.
     *
     * @param string $alternateNames
     *
     * @return Country
     */
    public function setAlternateNames($alternateNames)
    {
        $this->alternateNames = $alternateNames;

        return $this;
    }

    /**
     * Get alternateNames.
     *
     * @return string
     */
    public function getAlternateNames()
    {
        return $this->alternateNames;
    }

    /**
     * Set isoCode2.
     *
     * @param string $isoCode2
     *
     * @return Country
     */
    public function setIsoCode2($isoCode2)
    {
        $this->isoCode2 = $isoCode2;

        return $this;
    }

    /**
     * Get isoCode2.
     *
     * @return string
     */
    public function getIsoCode2()
    {
        return $this->isoCode2;
    }

    /**
     * Set isoCode3.
     *
     * @param string $isoCode3
     *
     * @return Country
     */
    public function setIsoCode3($isoCode3)
    {
        $this->isoCode3 = $isoCode3;

        return $this;
    }

    /**
     * Get isoCode3.
     *
     * @return string
     */
    public function getIsoCode3()
    {
        return $this->isoCode3;
    }

    /**
     * Set nbCode.
     *
     * @param int $nbCode
     *
     * @return Country
     */
    public function setNbCode($nbCode)
    {
        $this->nbCode = $nbCode;

        return $this;
    }

    /**
     * Get nbCode.
     *
     * @return int
     */
    public function getNbCode()
    {
        return $this->nbCode;
    }

    /**
     * Set fipsCode.
     */
    public function setFipsCode(?string $fipsCode): self
    {
        $this->fipsCode = $fipsCode;

        return $this;
    }

    /**
     * Get fipsCode.
     */
    public function getFipsCode(): ?string
    {
        return $this->fipsCode;
    }

    /**
     * Set fipsName.
     */
    public function setFipsName(?string $fipsName): self
    {
        $this->fipsName = $fipsName;

        return $this;
    }

    /**
     * Get fipsName.
     */
    public function getFipsName(): ?string
    {
        return $this->fipsName;
    }

    /**
     * Set region.
     *
     * @param string $region
     *
     * @return Country
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region.
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set subRegion.
     *
     * @param string $subRegion
     *
     * @return Country
     */
    public function setSubRegion($subRegion)
    {
        $this->subRegion = $subRegion;

        return $this;
    }

    /**
     * Get subRegion.
     *
     * @return string
     */
    public function getSubRegion()
    {
        return $this->subRegion;
    }

    /**
     * Set latitude.
     *
     * @param string $latitude
     *
     * @return Country
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude.
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude.
     *
     * @param string $longitude
     *
     * @return Country
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude.
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    public function getSalesAreas()
    {
        return $this->salesAreas;
    }

    public function addSalesArea(SalesArea $salesArea): self
    {
        $this->salesAreas->add($salesArea);

        return $this;
    }

    public function removeSalesArea(SalesArea $salesArea): self
    {
        $this->salesAreas->removeElement($salesArea);

        return $this;
    }

    public function getContinent(): ?Continent
    {
        return $this->continent;
    }

    /**
     * @return $this
     */
    public function setContinent(Continent $continent): self
    {
        $this->continent = $continent;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    #[Groups(['country_phone_code'])]
    public function getPhoneCode(): ?string
    {
        foreach (CountryCodeToRegionCodeMap::COUNTRY_CODE_TO_REGION_CODE_MAP as $phoneCode => $region) {
            if (\in_array($this->getIsoCode2(), $region, true)) {
                return \sprintf('+%d', $phoneCode);
            }
        }

        return null;
    }
}
