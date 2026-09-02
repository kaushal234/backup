<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
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
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\Location;
use App\Entity\Family;
use App\Filter\SimpleSearchFilter;
use App\Validator\Constraints\Location as ValidLocation;
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
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['family', 'catalogue_family', 'expose_legacy', 'people_public']],
        ),
        new Get(
            security: "is_granted('PRODUCT_FAMILY_VIEW_VOTER', object)",
        ),
        new Put(security: "is_granted('FEATURE_CATALOG_FAMILY_EDIT') or is_granted('MOO_CAT')"),
        new Delete(security: "is_granted('FEATURE_CATALOG_DELETE') or is_granted('MOO_CAT')"),
        new Post(security: "is_granted('FEATURE_CATALOG_CREATE') or is_granted('MOO_CAT')"),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['family', 'catalogue_family', 'catalogue_family_detail', 'expose_legacy', 'location_public', 'tag']],
    denormalizationContext: ['groups' => ['catalogue_family_write', 'tag_write']],
)]
#[ORM\Table(name: 'product_families')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC', 'id' => 'ASC'])]
#[ApiFilter(BooleanFilter::class)]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial', 'productType.englishName' => 'partial', 'productType.frenchName' => 'partial', 'productType.spanishName' => 'partial', 'productType.portugueseName' => 'partial', 'productType.russianName' => 'partial', 'productType.germanName' => 'partial', 'productType.chineseName' => 'partial', 'productType.japaneseName' => 'partial'])]
#[ApiFilter(SearchFilter::class, properties: ['productType' => 'exact', 'legacyId' => 'exact'])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['catalogue_family_list']])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'products_datasheets')]
class ProductFamily extends Family implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['catalogue_family', 'catalogue_family_detail', 'catalogue_family_write', 'catalogue_type_detail', 'catalogue_family_list', 'catalogue_product', 'catalogue_public'])]
    #[Legacy\Column(column: 'model')]
    protected string $name;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ProductType', inversedBy: 'productFamilies')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['catalogue_family', 'catalogue_family_detail', 'catalogue_family_write', 'catalogue_product', 'catalogue_public', 'equipment_record', 'equipment_record_detail', 'equipment_list_user', 'equipment_list_buyer'])]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?ProductType $productType = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column(type: 'text', length: 65000, nullable: true)]
    #[Groups(['catalogue_family', 'catalogue_family_detail', 'catalogue_family_write', 'catalogue_type_detail'])]
    #[Legacy\Column(column: 'fr')]
    private ?string $frenchDescription = null;

    #[ORM\Column(type: 'text', length: 65000, nullable: true)]
    #[Groups(['catalogue_family', 'catalogue_family_detail', 'catalogue_family_write', 'catalogue_type_detail'])]
    #[Legacy\Column(column: 'en')]
    private ?string $englishDescription = null;

    #[ORM\Column(type: 'text', length: 65000, nullable: true)]
    #[Groups(['catalogue_family', 'catalogue_family_detail', 'catalogue_family_write', 'catalogue_type_detail'])]
    #[Legacy\Column(column: 'es')]
    private ?string $spanishDescription = null;

    #[ORM\Column(type: 'text', length: 65000, nullable: true)]
    #[Groups(['catalogue_family', 'catalogue_family_detail', 'catalogue_family_write', 'catalogue_type_detail'])]
    #[Legacy\Column(column: 'pt')]
    private ?string $portugueseDescription = null;

    #[ORM\Column(type: 'text', length: 65000, nullable: true)]
    #[Groups(['catalogue_family', 'catalogue_family_detail', 'catalogue_family_write', 'catalogue_type_detail'])]
    #[Legacy\Column(column: 'zh', transformer: Utf8ToHtmlEntities::class)]
    private ?string $chineseDescription = null;

    #[ORM\Column(type: 'text', length: 65000, nullable: true)]
    #[Groups(['catalogue_family', 'catalogue_family_detail', 'catalogue_family_write', 'catalogue_type_detail'])]
    #[Legacy\Column(column: 'ja', transformer: Utf8ToHtmlEntities::class)]
    private ?string $japaneseDescription = null;

    #[ORM\Column(type: 'text', length: 65000, nullable: true)]
    #[Groups(['catalogue_family', 'catalogue_family_detail', 'catalogue_family_write', 'catalogue_type_detail'])]
    #[Legacy\Column(column: 'de')]
    private ?string $germanDescription = null;

    #[ORM\Column(type: 'text', length: 65000, nullable: true)]
    #[Groups(['catalogue_family', 'catalogue_family_detail', 'catalogue_family_write', 'catalogue_type_detail'])]
    #[Legacy\Column(column: 'ru', transformer: Utf8ToHtmlEntities::class)]
    private ?string $russianDescription = null;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['catalogue_family', 'catalogue_family_detail', 'catalogue_family_write', 'catalogue_type_detail'])]
    #[Legacy\Column(column: 'hidden', transformer: BooleanToInteger::class)]
    private bool $hidden = true;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['catalogue_family', 'catalogue_family_detail', 'catalogue_family_write', 'catalogue_type_detail'])]
    #[Legacy\Column(column: 'public', transformer: BooleanToInteger::class)]
    private bool $publicForTLD = false;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['catalogue_family', 'catalogue_family_detail', 'catalogue_family_write', 'catalogue_type_detail'])]
    private bool $publicForAerospecialties = false;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['catalogue_family', 'catalogue_family_detail', 'catalogue_family_write', 'catalogue_type_detail'])]
    private bool $publicForSAS = false;

    /**
     * @var Collection<ProductFamilyDMS>
     */
    #[ORM\OneToMany(mappedBy: 'family', targetEntity: 'App\Entity\Sales\ProductFamilyDMS', cascade: ['remove'])]
    #[Groups(['catalogue_family_detail', 'catalogue_type_detail'])]
    private Collection $productFamilyDMS;

    /**
     * @var Collection<Product>
     */
    #[ORM\OneToMany(mappedBy: 'family', targetEntity: 'App\Entity\Sales\Product', cascade: ['remove'])]
    #[Groups(['catalogue_family_detail'])]
    private Collection $products;

    /**
     * @var Collection<ProductFamilyTag>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\ProductFamilyTag', mappedBy: 'productFamilies')]
    #[Assert\Valid]
    #[Groups(['catalogue_family_detail', 'catalogue_family_write'])]
    private Collection $tags;

    #[ORM\ManyToMany(targetEntity: Location::class, inversedBy: 'manufacturedProductFamilies')]
    #[ORM\JoinTable(name: 'product_families_manufacturing_factories')]
    #[Groups(['catalogue_family_detail', 'catalogue_family_write', 'catalogue_family_manufacturing'])]
    #[Assert\All([
        new ValidLocation(factory: true),
    ])]
    private Collection $manufacturingFactories;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->productFamilyDMS = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->manufacturingFactories = new ArrayCollection();
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getProductType(): ProductType
    {
        return $this->productType;
    }

    public function setProductType(ProductType $productType): self
    {
        $this->productType = $productType;

        return $this;
    }

    public function getFrenchDescription(): ?string
    {
        return $this->frenchDescription;
    }

    public function setFrenchDescription(?string $frenchDescription): self
    {
        $this->frenchDescription = $frenchDescription;

        return $this;
    }

    public function getEnglishDescription(): ?string
    {
        return $this->englishDescription;
    }

    public function setEnglishDescription(?string $englishDescription): self
    {
        $this->englishDescription = $englishDescription;

        return $this;
    }

    public function getSpanishDescription(): ?string
    {
        return $this->spanishDescription;
    }

    public function setSpanishDescription(?string $spanishDescription): self
    {
        $this->spanishDescription = $spanishDescription;

        return $this;
    }

    public function getPortugueseDescription(): ?string
    {
        return $this->portugueseDescription;
    }

    public function setPortugueseDescription(?string $portugueseDescription): self
    {
        $this->portugueseDescription = $portugueseDescription;

        return $this;
    }

    public function getChineseDescription(): ?string
    {
        return $this->chineseDescription;
    }

    public function setChineseDescription(?string $chineseDescription): self
    {
        $this->chineseDescription = $chineseDescription;

        return $this;
    }

    public function getJapaneseDescription(): ?string
    {
        return $this->japaneseDescription;
    }

    public function setJapaneseDescription(?string $japaneseDescription): self
    {
        $this->japaneseDescription = $japaneseDescription;

        return $this;
    }

    public function getGermanDescription(): ?string
    {
        return $this->germanDescription;
    }

    public function setGermanDescription(?string $germanDescription): self
    {
        $this->germanDescription = $germanDescription;

        return $this;
    }

    public function getRussianDescription(): ?string
    {
        return $this->russianDescription;
    }

    public function setRussianDescription(?string $russianDescription): self
    {
        $this->russianDescription = $russianDescription;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * @return Collection<Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * @return Collection<ProductFamilyDMS>
     */
    public function getProductFamilyDMS(): Collection
    {
        return $this->productFamilyDMS;
    }

    #[Groups(['catalogue_family_detail', 'catalogue_type_detail', 'catalogue_product_detail', 'demo_detail'])]
    #[SerializedName('dmsPhoto')]
    public function getDMSPhoto(): ?int
    {
        $productFamilyDMS = $this->getProductFamilyDMS()->filter(static fn (ProductFamilyDMS $familyDMS) => ProductFamilyDMS::PHOTOS === $familyDMS->getDmsType())->first();

        return false === $productFamilyDMS ? null : $productFamilyDMS->getDms()->getLegacyId();
    }

    public function getDMSLineDrawing(): ?int
    {
        $productFamilyDMS = $this->getProductFamilyDMS()->filter(static fn (ProductFamilyDMS $familyDMS) => ProductFamilyDMS::LINE_DRAWING === $familyDMS->getDmsType())->first();

        return false === $productFamilyDMS ? null : $productFamilyDMS->getDms()->getLegacyId();
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

    /**
     * @return Collection<ProductFamilyTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(ProductFamilyTag $tag): self
    {
        $tag->addProductFamily($this);
        $this->tags->add($tag);

        return $this;
    }

    public function removeTag(ProductFamilyTag $tag): self
    {
        $tag->removeProductFamily($this);
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<Location>
     */
    public function getManufacturingFactories(): Collection
    {
        return $this->manufacturingFactories;
    }

    public function addManufacturingFactory(Location $factory): self
    {
        if (!$this->manufacturingFactories->contains($factory)) {
            $this->manufacturingFactories->add($factory);
        }

        return $this;
    }

    public function removeManufacturingFactory(Location $factory): self
    {
        $this->manufacturingFactories->removeElement($factory);

        return $this;
    }

    public function isRanger(): bool
    {
        return $this->manufacturingFactories->count() > 1;
    }
}
