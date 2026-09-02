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
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Finance\FinanceFamily;
use App\Entity\Manufacturing\ManufacturingFamily;
use App\Entity\Manufacturing\ProductManufacturing;
use App\Entity\Manufacturing\ProductStandardItem;
use App\Entity\Sales\AircraftCompatibility\AircraftCompatibility;
use App\Filter\SimpleSearchFilter;
use App\Link\Mapping\Attributes\LinkField;
use App\Link\Resource\LinkResourceInterface;
use App\Link\Resource\LinkResourceTrait;
use App\Serializer\Filter\ContextFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
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
            openapi: true,
            normalizationContext: ['groups' => ['location_public', 'catalogue_product', 'expose_legacy', 'family']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('AUTHORIZED_APPLICATION_FEATURE_CATALOG_DOWNLOAD') or is_granted('ACCESS_EXTRANET_USER')",
        ),
        new GetCollection(
            uriTemplate: '/aircraft_compatibilities/{id}/products',
            uriVariables: [
                'id' => new Link(fromProperty: 'products', fromClass: AircraftCompatibility::class),
            ],
            normalizationContext: ['groups' => ['catalogue_public']],
        ),
        new Post(
            denormalizationContext: ['groups' => ['catalogue_product_write']],
            security: "is_granted('FEATURE_CATALOG_CREATE') or is_granted('MOO_CAT')",
        ),
        new Get(
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
        ),
        new Put(security: "is_granted('CATALOG_ADMIN_VOTER', object)"),
        new Delete(security: "is_granted('FEATURE_CATALOG_DELETE') or is_granted('MOO_CAT')"),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['location_public', 'catalogue_product', 'catalogue_product_detail', 'expose_legacy', 'family', 'finance_family', 'manufacturing_family']],
    denormalizationContext: ['groups' => []],
)]
#[ORM\Table(name: 'products')]
#[ORM\UniqueConstraint(name: 'unique_product_name', columns: ['name'])]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC', 'id', 'family.name', 'family.productType.englishName'])]
#[ApiFilter(BooleanFilter::class)]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial', 'family.name' => 'partial'])]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'name' => 'exact', 'family' => 'exact', 'legacyId' => 'exact', 'financeFamily' => 'exact', 'manufacturingFamily' => 'exact'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['product_list', 'product_export', 'product_list_pricing', 'product_manufacturing']])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalization_groups', 'whitelist' => ['catalogue_family_manufacturing']])]
#[ApiFilter(ContextFilter::class)]
#[App\Loggable]
#[Legacy\Synchronize(table: 'models')]
class Product implements \Stringable, LegacyIdInterface, LinkResourceInterface
{
    use LegacyIdentifierTrait;
    use LinkResourceTrait;

    final public const INNOVATIVE_LEVELS = ['Introduction', 'Major Redesign'];

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['catalogue_product', 'catalogue_family_detail', 'product_export', 'product_manufacturing'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ProductFamily', inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['catalogue_product', 'catalogue_public', 'equipment_record', 'equipment_record_detail', 'equipment_list_user', 'equipment_list_buyer', 'product_restricted', 'catalogue_product_write', 'sales_forecast_detail'])]
    #[Legacy\Column(column: 'family', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'productType.legacyId'])]
    private ProductFamily $family;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['product_list', 'catalogue_public', 'catalogue_product', 'catalogue_product_write', 'catalogue_family_detail', 'equipment_list_user', 'equipment_list_buyer', 'demo_list', 'sfr_export', 'product_restricted', 'product_export', 'product_list_pricing', 'product_manufacturing'])]
    #[Legacy\Column(column: 'model')]
    #[Legacy\Copy(table: 'ccr', columns: ['model'])]
    #[Legacy\Copy(table: 'cor_prod', columns: ['model'])]
    #[Legacy\Copy(table: 'cpr', columns: ['model'])]
    #[Legacy\Copy(table: 'demerit', columns: ['model'])]
    #[Legacy\Copy(table: 'eap', columns: ['model'])]
    #[Legacy\Copy(table: 'gwf', columns: ['model'])]
    #[Legacy\Copy(table: 'manuals', columns: ['model'])]
    #[Legacy\Copy(table: 'meap', columns: ['model'])]
    #[Legacy\Copy(table: 'mod_models', columns: ['model'])]
    #[Legacy\Copy(table: 'ncr', columns: ['model'])]
    #[Legacy\Copy(table: 'nto_models', columns: ['model'])]
    #[Legacy\Copy(table: 'pip', columns: ['model'])]
    #[Legacy\Copy(table: 'products_datasheets', columns: ['model'])]
    #[Legacy\Copy(table: 'sb_coverage', columns: ['model'])]
    #[Legacy\Copy(table: 'sbs_lines', columns: ['model'])]
    #[Legacy\Copy(table: 'service', columns: ['model'])]
    #[Legacy\Copy(table: 'service_serials', columns: ['model'])]
    #[Legacy\Copy(table: 'sfr', columns: ['model'])]
    #[Legacy\Copy(table: 'sor_lines', columns: ['model'])]
    #[Legacy\Copy(table: 'warranty', columns: ['model'])]
    #[LinkField(fields: ['name'])]
    private string $name;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['catalogue_product', 'catalogue_family_detail', 'catalogue_product_write'])]
    #[Legacy\Column(column: 'hide')]
    private bool $hidden = true;

    /**
     * @var bool Is the ER of this kind product will have a lighter GT process
     */
    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['catalogue_product_detail', 'catalogue_product_write'])]
    #[Legacy\Column(column: 'light')]
    private bool $light = false;

    #[ORM\Column(type: 'text', length: 65000, nullable: true)]
    #[Groups(['catalogue_product_write', 'catalogue_product_detail'])]
    private ?string $description = '';

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn]
    #[Groups(['catalogue_product', 'catalogue_family_detail', 'catalogue_product_write'])]
    #[Legacy\Column(column: 'erpid', transformer: ObjectToProperty::class, options: ['property' => 'erp', 'nullValue' => 0])]
    private ?Location $erpLocation = null;

    /**
     * @var Collection<EquipmentRecord>
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: 'App\Entity\EquipmentRecord')]
    private Collection $equipmentRecords;

    /**
     * @var Collection<ProductDMS>
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: 'App\Entity\Sales\ProductDMS', cascade: ['remove'])]
    #[Groups(['catalogue_product_detail'])]
    private Collection $productDMS;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Finance\FinanceFamily')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['catalogue_product', 'catalogue_product_detail', 'product_list_pricing', 'catalogue_product_write', 'catalogue_product_finance_admin'])]
    #[Legacy\Column(column: 'finance_family', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    private ?FinanceFamily $financeFamily = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Manufacturing\ManufacturingFamily')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['catalogue_product', 'catalogue_product_detail', 'catalogue_product_write'])]
    private ?ManufacturingFamily $manufacturingFamily = null;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Manufacturing\ProductManufacturing', mappedBy: 'product', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['product_manufacturing', 'product_manufacturing:write'])]
    private Collection $productManufacturings;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Manufacturing\ProductStandardItem', mappedBy: 'product', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\Valid]
    #[Groups(['catalogue_product_detail', 'catalogue_product_write', 'product_standard_item_write'])]
    private Collection $productStandardItems;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    #[Assert\Choice(choices: self::INNOVATIVE_LEVELS)]
    #[Groups(['catalogue_product_write', 'catalogue_product_detail', 'catalogue_family_detail'])]
    #[Legacy\Column(column: 'innovative_level')]
    private ?string $innovativeLevel = null;

    public function __construct()
    {
        $this->equipmentRecords = new ArrayCollection();
        $this->productDMS = new ArrayCollection();
        $this->productManufacturings = new ArrayCollection();
        $this->productStandardItems = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFamily(): ProductFamily
    {
        return $this->family;
    }

    public function setFamily(ProductFamily $family): self
    {
        $this->family = $family;

        return $this;
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

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getErpLocation(): Location
    {
        return $this->erpLocation;
    }

    public function setErpLocation(Location $erpLocation): self
    {
        $this->erpLocation = $erpLocation;

        return $this;
    }

    /**
     * @return Collection<EquipmentRecord>
     */
    public function getEquipmentRecords(): Collection
    {
        return $this->equipmentRecords;
    }

    /**
     * @return Collection<ProductDMS>
     */
    public function getProductDMS(): Collection
    {
        return $this->productDMS;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getFinanceFamily(): ?FinanceFamily
    {
        return $this->financeFamily;
    }

    public function setFinanceFamily(?FinanceFamily $financeFamily): self
    {
        $this->financeFamily = $financeFamily;

        return $this;
    }

    public function isLight(): bool
    {
        return $this->light;
    }

    public function setLight(bool $light): self
    {
        $this->light = $light;

        return $this;
    }

    public function getProductManufacturings(): Collection
    {
        return $this->productManufacturings;
    }

    public function addProductManufacturing(ProductManufacturing $productManufacturing): self
    {
        $productManufacturing->setProduct($this);
        $this->productManufacturings->add($productManufacturing);

        return $this;
    }

    public function removeProductManufacturing(ProductManufacturing $productManufacturing)
    {
        $this->productManufacturings->removeElement($productManufacturing);

        return $this;
    }

    public function getProductStandardItems(): Collection
    {
        return $this->productStandardItems;
    }

    public function addProductStandardItem(ProductStandardItem $standardItem): self
    {
        $standardItem->setProduct($this);
        if (!$this->productStandardItems->contains($standardItem)) {
            $this->productStandardItems->add($standardItem);
        }

        return $this;
    }

    public function removeProductStandardItem(ProductStandardItem $standardItem): self
    {
        if ($this->productStandardItems->contains($standardItem)) {
            $this->productStandardItems->removeElement($standardItem);
        }

        return $this;
    }

    #[Groups('product_export')]
    #[SerializedName('hidden')]
    public function getHiddenForExport(): string
    {
        return $this->hidden ? 'yes' : 'no';
    }

    #[Groups('product_export')]
    #[SerializedName('erpLocation')]
    public function getErpLocationForExport(): string
    {
        return $this->erpLocation->getName();
    }

    #[Groups('product_export')]
    #[SerializedName('family')]
    public function getFamilyForExport(): string
    {
        return $this->family->getName();
    }

    #[Groups('product_export')]
    #[SerializedName('financeFamily')]
    public function getFinanceFamilyExport()
    {
        return null !== $this->financeFamily ? $this->financeFamily->getName() : '';
    }

    #[Groups('product_export')]
    #[SerializedName('productType')]
    public function getProductTypeExport(): string
    {
        return $this->family->getProductType()->getEnglishName();
    }

    #[Groups('product_export')]
    #[SerializedName('productTypeId')]
    public function getProductTypeId(): int
    {
        return $this->family->getProductType()->getId();
    }

    #[Groups('product_export')]
    #[SerializedName('productTypeDMSPhoto')]
    public function getProductTypeDMSPhoto(): ?int
    {
        return null !== $this->family->getProductType()->getDms() ? $this->family->getProductType()->getDms()->getId() : null;
    }

    #[Groups('product_export')]
    #[SerializedName('familyDMSPhoto')]
    public function getFamilyDMSPhoto(): int
    {
        return $this->family->getDMSPhoto();
    }

    #[Groups(['product_export'])]
    #[SerializedName('familyId')]
    public function getFamilyId(): int
    {
        return $this->family->getId();
    }

    #[Groups(['catalogue_product_detail', 'demo_detail', 'product_export'])]
    #[SerializedName('dmsPhoto')]
    public function getDMSPhoto()
    {
        $productDms = $this->getProductDMS()->filter(static fn (ProductDMS $productDMS) => ProductFamilyDMS::PHOTOS === $productDMS->getDmsType())->first();

        return false === $productDms ? null : $productDms->getDms()->getLegacyId();
    }

    public function getManufacturingFamily(): ?ManufacturingFamily
    {
        return $this->manufacturingFamily;
    }

    public function setManufacturingFamily(?ManufacturingFamily $manufacturingFamily): self
    {
        $this->manufacturingFamily = $manufacturingFamily;

        return $this;
    }

    public function getInnovativeLevel(): ?string
    {
        return $this->innovativeLevel;
    }

    public function setInnovativeLevel(?string $innovativeLevel): self
    {
        $this->innovativeLevel = $innovativeLevel;

        return $this;
    }
}
