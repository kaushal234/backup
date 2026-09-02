<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\FreeTextQueryFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\DMS;
use App\Filter\SimpleSearchFilter;
use App\Validator\Constraints\LockedValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\BooleanToInteger;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['englishName', 'frenchName', 'spanishName', 'portugueseName', 'chineseName', 'japaneseName', 'germanName', 'russianName'])]
#[ApiResource(
    operations: [
        new GetCollection(
            openapi: true,
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
            parameters: [
                'autocomplete' => new QueryParameter(
                    filter: new FreeTextQueryFilter(new OrFilter(new PartialSearchFilter())),
                    description: 'To allow filtering by partial names.',
                    properties: ['englishName', 'frenchName', 'spanishName', 'portugueseName', 'chineseName', 'germanName'],
                ),
            ],
        ),
        new Post(
            normalizationContext: ['groups' => ['catalogue_type', 'catalogue_type_detail', 'expose_legacy', 'people_public', 'document']],
            security: "is_granted('FEATURE_CATALOG_TYPE_CREATE') or is_granted('MOO_CAT')",
        ),
        new Get(
            normalizationContext: ['groups' => ['catalogue_type', 'catalogue_type_detail', 'expose_legacy', 'people_public', 'document']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')"),
        new Put(
            normalizationContext: ['groups' => ['catalogue_type', 'catalogue_type_detail', 'expose_legacy', 'document']],
            security: "is_granted('FEATURE_CATALOG_TYPE_EDIT') or is_granted('MOO_CAT')",
        ),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['catalogue_type', 'expose_legacy', 'people_public', 'document']],
    denormalizationContext: ['groups' => ['catalogue_type_write']],
)]
#[ORM\Table(name: 'product_types')]
#[ApiFilter(OrderFilter::class, properties: ['englishName', 'id'])]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'legacyId', 'englishName'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['legacyId' => 'exact', 'englishName' => 'partial'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['catalogue_type_list']])]
#[App\Loggable]
#[LockedValue(value: ProductType::PRODUCT_TYPE_ACU, propertyPath: 'englishName')]
#[LockedValue(value: ProductType::PRODUCT_TYPE_FIXED_ACU, propertyPath: 'englishName')]
#[LockedValue(value: ProductType::PRODUCT_TYPE_DISTRIBUTION_SYSTEMS, propertyPath: 'englishName')]
#[LockedValue(value: ProductType::PRODUCT_TYPE_POWER_CONVERSION, propertyPath: 'englishName')]
#[LockedValue(value: ProductType::PRODUCT_CONVENTIONAL_AIRCRAFT_TRACTORS, propertyPath: 'englishName')]
#[Legacy\Synchronize(table: 'products_categories')]
class ProductType implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    /**
     * @var string
     */
    final public const PRODUCT_TYPE_ACU = 'Air Conditioners';
    /**
     * @var string
     */
    final public const PRODUCT_TYPE_FIXED_ACU = 'Fixed Air Conditioning systems';
    /**
     * @var string
     */
    final public const PRODUCT_TYPE_DISTRIBUTION_SYSTEMS = 'Distribution Systems';
    /**
     * @var string
     */
    final public const PRODUCT_TYPE_POWER_CONVERSION = 'Power Conversion';
    /**
     * @var string
     */
    final public const PRODUCT_CONVENTIONAL_AIRCRAFT_TRACTORS = 'Conventional Aircraft Tractors';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['catalogue_type', 'catalogue_type_detail', 'catalogue_family_detail'])]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['catalogue_type', 'catalogue_type_detail', 'catalogue_type_write', 'catalogue_family', 'catalogue_family_detail', 'catalogue_type_list', 'catalogue_product', 'catalogue_public', 'equipment_record', 'equipment_record_detail', 'equipment_list_user', 'equipment_list_buyer', 'product_export'])]
    #[Legacy\Column(column: 'en')]
    #[Legacy\Copy(table: 'cor_prod', columns: ['type'])]
    #[Legacy\Copy(table: 'service', columns: ['type'])]
    #[Legacy\Copy(table: 'gwf', columns: ['type'])]
    #[Legacy\Copy(table: 'demerit', columns: ['product_type'])]
    #[Legacy\Copy(table: 'eap', columns: ['type'])]
    #[Legacy\Copy(table: 'meap', columns: ['product_type'])]
    #[Legacy\Copy(table: 'pip', columns: ['product_type'])]
    #[Legacy\Copy(table: 'warranty', columns: ['type'])]
    private ?string $englishName = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['catalogue_type', 'catalogue_type_detail', 'catalogue_type_write', 'catalogue_family_detail'])]
    #[Legacy\Column(column: 'fr')]
    private ?string $frenchName = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['catalogue_type', 'catalogue_type_detail', 'catalogue_type_write', 'catalogue_family_detail'])]
    #[Legacy\Column(column: 'es')]
    private ?string $spanishName = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['catalogue_type', 'catalogue_type_detail', 'catalogue_type_write', 'catalogue_family_detail'])]
    #[Legacy\Column(column: 'pt')]
    private ?string $portugueseName = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['catalogue_type', 'catalogue_type_detail', 'catalogue_type_write', 'catalogue_family_detail'])]
    #[Legacy\Column(column: 'zh', transformer: Utf8ToHtmlEntities::class)]
    private ?string $chineseName = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['catalogue_type', 'catalogue_type_detail', 'catalogue_type_write', 'catalogue_family_detail'])]
    #[Legacy\Column(column: 'ja', transformer: Utf8ToHtmlEntities::class)]
    private ?string $japaneseName = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['catalogue_type', 'catalogue_type_detail', 'catalogue_type_write', 'catalogue_family_detail'])]
    #[Legacy\Column(column: 'de')]
    private ?string $germanName = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['catalogue_type', 'catalogue_type_detail', 'catalogue_type_write', 'catalogue_family_detail'])]
    #[Legacy\Column(column: 'ru', transformer: Utf8ToHtmlEntities::class)]
    private ?string $russianName = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\DMS')]
    #[Groups(['catalogue_type', 'catalogue_type_detail', 'catalogue_type_write', 'catalogue_family_detail'])]
    #[Legacy\Column(column: 'dms_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?DMS $dms = null;

    /**
     * @var Collection<ProductFamily>
     */
    #[ORM\OneToMany(mappedBy: 'productType', targetEntity: 'App\Entity\Sales\ProductFamily')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    #[Groups(['catalogue_type_detail'])]
    private Collection $productFamilies;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['catalogue_type', 'catalogue_type_detail', 'catalogue_type_write'])]
    #[Legacy\Column(column: 'public', transformer: BooleanToInteger::class)]
    private bool $publicForTLD = false;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['catalogue_type', 'catalogue_type_detail', 'catalogue_type_write'])]
    private bool $publicForAerospecialties = false;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['catalogue_type', 'catalogue_type_detail', 'catalogue_type_write'])]
    private bool $publicForSAS = false;

    public function __construct()
    {
        $this->productFamilies = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEnglishName(): ?string
    {
        return $this->englishName;
    }

    public function setEnglishName(?string $englishName): self
    {
        $this->englishName = $englishName;

        return $this;
    }

    public function getFrenchName(): ?string
    {
        return $this->frenchName;
    }

    public function setFrenchName(?string $frenchName): self
    {
        $this->frenchName = $frenchName;

        return $this;
    }

    public function getSpanishName(): ?string
    {
        return $this->spanishName;
    }

    public function setSpanishName(?string $spanishName): self
    {
        $this->spanishName = $spanishName;

        return $this;
    }

    public function getPortugueseName(): ?string
    {
        return $this->portugueseName;
    }

    public function setPortugueseName(?string $portugueseName): self
    {
        $this->portugueseName = $portugueseName;

        return $this;
    }

    public function getChineseName(): ?string
    {
        return $this->chineseName;
    }

    public function setChineseName(?string $chineseName): self
    {
        $this->chineseName = $chineseName;

        return $this;
    }

    public function getJapaneseName(): ?string
    {
        return $this->japaneseName;
    }

    public function setJapaneseName(?string $japaneseName): self
    {
        $this->japaneseName = $japaneseName;

        return $this;
    }

    public function getGermanName(): ?string
    {
        return $this->germanName;
    }

    public function setGermanName(?string $germanName): self
    {
        $this->germanName = $germanName;

        return $this;
    }

    public function getRussianName(): ?string
    {
        return $this->russianName;
    }

    public function setRussianName(?string $russianName): self
    {
        $this->russianName = $russianName;

        return $this;
    }

    /**
     * @return Collection<ProductFamily>
     */
    public function getProductFamilies(): Collection
    {
        return $this->productFamilies;
    }

    public function getDms(): ?DMS
    {
        return $this->dms;
    }

    public function setDms(?DMS $dms): self
    {
        $this->dms = $dms;

        return $this;
    }

    public function isPublicForTLD(): bool
    {
        return $this->publicForTLD;
    }

    public function setPublicForTLD(bool $publicForTLD): self
    {
        $this->publicForTLD = $publicForTLD;

        return $this;
    }

    public function isPublicForAerospecialties(): bool
    {
        return $this->publicForAerospecialties;
    }

    public function setPublicForAerospecialties(bool $publicForAerospecialties): self
    {
        $this->publicForAerospecialties = $publicForAerospecialties;

        return $this;
    }

    public function isPublicForSAS(): bool
    {
        return $this->publicForSAS;
    }

    public function setPublicForSAS(bool $publicForSAS): self
    {
        $this->publicForSAS = $publicForSAS;

        return $this;
    }
}
