<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
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
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\AddressWithCountry;
use App\Entity\Finance\Currency;
use App\Entity\Quality\LocationArea;
use App\Entity\Sales\ProductFamily;
use App\Filter\Directory\OrLocationCapabilityFilter;
use App\Filter\Purchasing\VendorUserLocationFilter;
use App\Filter\SimpleSearchFilter;
use App\Link\Formatter\FormatterLinkLocationName;
use App\Link\Mapping\Attributes\LinkField;
use App\Link\Resource\LinkResourceInterface;
use App\Link\Resource\LinkResourceTrait;
use App\Validator\Constraints as AppAssert;
use App\Validator\Constraints\LockedValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\BooleanToChar;
use LegacyBundle\Doctrine\Transformer\CountryToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A location in the company.
 */
#[ORM\Entity(repositoryClass: 'App\Repository\Directory\LocationRepository')]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(
            openapi: true,
            normalizationContext: ['groups' => ['location', 'expose_legacy', 'network', 'currency']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
        ),
        new Post(security: "is_granted('FEATURE_LOCATION_WRITE')"),
        new Get(
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
        ),
        new Put(security: "is_granted('FEATURE_LOCATION_WRITE')"),
        new Delete(security: "is_granted('FEATURE_LOCATION_WRITE')"),
    ],
    normalizationContext: ['groups' => ['location_detail', 'expose_legacy', 'people_public', 'network', 'file:light']],
    denormalizationContext: ['groups' => ['location_write', 'address_write']],
)]
#[ORM\Table(name: 'directory_location')]
#[ORM\Index(columns: ['erp'])]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(ExistsFilter::class, properties: ['contact.serviceHubTelephone'])]
#[ApiFilter(BooleanFilter::class, properties: ['capability.sso', 'capability.factory', 'capability.warehouse', 'capability.sparePartsHub', 'capability.serviceHub', 'capability.headQuarter', 'state.public', 'erpInLN', 'state.hidden', 'state.disabled'])]
#[ApiFilter(SearchFilter::class, properties: ['businessUnit.region.subDivision.division.name', 'legacyId', 'name', 'network', 'erp', 'juridicalLocation', 'id'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial', 'company' => 'partial', 'erp' => 'exact'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroupsOverride', 'overrideDefaultGroups' => true, 'whitelist' => ['location_public', 'currency']])]
#[ApiFilter(VendorUserLocationFilter::class)]
#[ApiFilter(OrLocationCapabilityFilter::class)]
#[App\Loggable]
#[Legacy\Synchronize(table: 'locations')]
#[LockedValue(value: Location::TRACTEASY, propertyPath: 'name')]
class Location implements \Stringable, LegacyIdInterface, LinkResourceInterface
{
    use LegacyIdentifierTrait;
    use LinkResourceTrait;

    final public const string TLD_ERP_SOFTWARE = 'LN';
    final public const string SAGEPARTS_ERP_SOFTWARE = 'P21';
    final public const string ADHETEC_ERP_SOFTWARE = 'PROGINOV';
    final public const string AES_ERP_SOFTWARE = 'INFOR EAM';
    final public const string TRACTEASY = 'TRACTEASY';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['location', 'location_detail'])]
    private ?int $id = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column(type: 'string', length: 50, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['location', 'location_public', 'location_detail', 'location_write', 'acl', 'location_areas', 'location_areas_detail', 'tool_detail', 'group_member', 'quotation', 'quotation:detail', 'business_unit_detail', 'sfr_export', 'product_export', 'product_manufacturing', 'user:me'])]
    #[Legacy\Column(column: 'location')]
    #[Legacy\Copy(table: 'service', columns: ['sales_org', 'sso_service', 'man_location'])]
    #[Legacy\Copy(table: 'warranty', columns: ['sales_org', 'man_location'])]
    #[Legacy\Copy(table: 'pi_family_matrix', columns: ['factory'])]
    #[LinkField(fields: ['name'], transformer: FormatterLinkLocationName::class)]
    private string $name;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(['location', 'location_detail', 'location_write', 'acl', 'location_areas', 'location_areas_detail', 'tool_detail', 'quotation', 'quotation:detail', 'business_unit_detail'])]
    #[Legacy\Column(column: 'company_name')]
    private string $company;

    #[ORM\Embedded(class: '\App\Entity\Directory\LocationCapability')]
    #[Assert\NotBlank]
    #[Assert\Valid]
    #[Groups(['location', 'location_detail', 'location_write', 'user:me'])]
    #[Legacy\EmbeddedColumn(property: 'sso', column: 'role', transformer: BooleanToChar::class, options: ['trueValue' => 'SSO', 'falseValue' => 'ERP'])]
    #[Legacy\EmbeddedColumn(property: 'factory', column: 'factory', transformer: BooleanToChar::class)]
    #[Legacy\EmbeddedColumn(property: 'warehouse', column: 'warehouse', transformer: BooleanToChar::class)]
    #[Legacy\EmbeddedColumn(property: 'sparePartsHub', column: 'sph', transformer: BooleanToChar::class)]
    #[Legacy\EmbeddedColumn(property: 'serviceHub', column: 'sh', transformer: BooleanToChar::class)]
    #[Legacy\EmbeddedColumn(property: 'headQuarter', column: 'hq', transformer: BooleanToChar::class)]
    private LocationCapability $capability;

    #[ORM\Embedded(class: '\App\Entity\Directory\LocationContact')]
    #[Assert\Valid]
    #[Groups(['location', 'location_detail', 'location_write'])]
    #[Legacy\EmbeddedColumn(property: 'telephone', column: 'tel')]
    #[Legacy\EmbeddedColumn(property: 'fax', column: 'fax')]
    #[Legacy\EmbeddedColumn(property: 'serviceHubEmail', column: 'sh_email')]
    #[Legacy\EmbeddedColumn(property: 'serviceHubTelephone', column: 'sh_tel')]
    #[Legacy\EmbeddedColumn(property: 'sparePartsEmail', column: 'sph_email')]
    #[Legacy\EmbeddedColumn(property: 'sparePartsTelephone', column: 'parts_tel')]
    #[Legacy\EmbeddedColumn(property: 'sparePartsFax', column: 'parts_fax')]
    private LocationContact $contact;

    #[ORM\Embedded(class: '\App\Entity\Directory\LocationState')]
    #[Assert\Valid]
    #[Groups(['location', 'location_detail', 'location_write'])]
    #[Legacy\EmbeddedColumn(property: 'public', column: 'public', transformer: BooleanToChar::class, options: ['trueValue' => 1, 'falseValue' => 0])]
    #[Legacy\EmbeddedColumn(property: 'hidden', column: 'hidden', transformer: BooleanToChar::class, options: ['trueValue' => 1, 'falseValue' => 0])]
    #[Legacy\EmbeddedColumn(property: 'disabled', column: 'disable', transformer: BooleanToChar::class, options: ['trueValue' => 1, 'falseValue' => 0])]
    private LocationState $state;

    #[ORM\Embedded(class: '\App\Entity\AddressWithCountry')]
    #[Assert\Valid]
    #[Groups(['location_detail', 'location_write', 'address_write', 'people_detail', 'location_address'])]
    #[Legacy\EmbeddedColumn(property: 'street1', column: 'street1')]
    #[Legacy\EmbeddedColumn(property: 'street2', column: 'street2')]
    #[Legacy\EmbeddedColumn(property: 'postalCode', column: 'postal_code')]
    #[Legacy\EmbeddedColumn(property: 'city', column: 'city')]
    #[Legacy\EmbeddedColumn(property: 'town', column: 'town')]
    #[Legacy\EmbeddedColumn(property: 'country', column: 'country', transformer: CountryToString::class)]
    private AddressWithCountry $address;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type('string')]
    #[Assert\Length(max: 255)]
    #[Groups(['location', 'location_detail', 'location_write'])]
    #[Legacy\Column(column: 'email_domain')]
    private ?string $domain = null;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['location', 'location_detail', 'location_write'])]
    #[Legacy\Column(column: 'fw_inside_network')]
    private ?string $internalNetworkAddress = null;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Assert\Type(type: 'integer')]
    #[Groups(['customer_relationship_team_detail', 'location', 'location_public', 'location_detail', 'location_write', 'quotation', 'quotation:detail', 'business_unit_detail', 'user:me', 'product_export', 'outbound_request:qr_code'])]
    #[Legacy\Column(column: 'erp')]
    private ?int $erp = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    #[Assert\Type(type: 'bool')]
    #[Groups(['location', 'location_detail', 'location_write'])]
    private bool $erpInLN = false;

    #[ORM\OneToOne(mappedBy: 'location', targetEntity: 'App\Entity\Directory\BusinessUnit')]
    #[Assert\Valid]
    #[Groups(['location_detail', 'location_write'])]
    private ?BusinessUnit $businessUnit = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\JuridicalLocation', inversedBy: 'locations')]
    #[Assert\Valid]
    #[Groups(['location_detail', 'location_write'])]
    #[Legacy\Column(column: 'juridical_location_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?JuridicalLocation $juridicalLocation = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['location_detail', 'location_write'])]
    #[Transferable]
    #[Legacy\Column(column: 'repid', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?People $representative = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Finance\Currency')]
    #[Groups(['location', 'location_detail', 'location_write', 'currency', 'user:me', 'people_detail', 'sfr_export'])]
    #[Legacy\Column(column: 'dcur', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    private ?Currency $currency = null;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(type: 'string', length: 40, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 40)]
    #[Groups(['location', 'location_detail', 'location_write', 'user:me'])]
    #[AppAssert\TimeZone]
    #[Legacy\Column(column: 'timezone')]
    private ?string $timeZone = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[ApiProperty(iris: ['https://schema.org/url'])]
    #[Assert\Url(requireTld: true)]
    #[Groups(['location_detail', 'people_detail', 'location_write'])]
    private ?string $publicWebsite = null;

    /**
     * one location has many LocationArea.
     *
     * @var Collection<LocationArea>
     */
    #[Groups(['hidden'])]
    #[ORM\OneToMany(mappedBy: 'factory', targetEntity: '\App\Entity\Quality\LocationArea')]
    private Collection $locationAreas;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Network')]
    #[Groups(['location', 'location_detail', 'location_write', 'network'])]
    private ?Network $network = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Choice(choices: [self::TLD_ERP_SOFTWARE, self::SAGEPARTS_ERP_SOFTWARE, self::ADHETEC_ERP_SOFTWARE, self::AES_ERP_SOFTWARE])]
    #[Groups(['location_detail', 'location_write'])]
    private ?string $erpSoftware = null;

    #[ORM\ManyToMany(targetEntity: ProductFamily::class, mappedBy: 'manufacturingFactories')]
    private Collection $manufacturedProductFamilies;

    public function __construct()
    {
        $this->capability = new LocationCapability();
        $this->address = new AddressWithCountry();
        $this->contact = new LocationContact();
        $this->state = new LocationState();
        $this->locationAreas = new ArrayCollection();
        $this->manufacturedProductFamilies = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
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

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getCapability(): LocationCapability
    {
        return $this->capability;
    }

    public function setCapability(LocationCapability $capability): self
    {
        $this->capability = $capability;

        return $this;
    }

    public function getContact(): LocationContact
    {
        return $this->contact;
    }

    public function setContact(LocationContact $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getState(): LocationState
    {
        return $this->state;
    }

    public function setState(LocationState $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(?string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getInternalNetworkAddress(): ?string
    {
        return $this->internalNetworkAddress;
    }

    public function setInternalNetworkAddress(?string $internalNetworkAddress): self
    {
        $this->internalNetworkAddress = $internalNetworkAddress;

        return $this;
    }

    public function getAddress(): AddressWithCountry
    {
        return $this->address;
    }

    public function setAddress(AddressWithCountry $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getErp(): ?int
    {
        return $this->erp;
    }

    public function setErp(?int $erp): self
    {
        $this->erp = $erp;

        return $this;
    }

    public function isErpInLN(): bool
    {
        return $this->erpInLN;
    }

    public function setErpInLN(bool $erpInLN): self
    {
        $this->erpInLN = $erpInLN;

        return $this;
    }

    public function getBusinessUnit(): ?BusinessUnit
    {
        return $this->businessUnit;
    }

    public function setBusinessUnit(?BusinessUnit $businessUnit): self
    {
        $this->businessUnit = $businessUnit;

        return $this;
    }

    public function getJuridicalLocation(): ?JuridicalLocation
    {
        return $this->juridicalLocation;
    }

    public function setJuridicalLocation(?JuridicalLocation $juridicalLocation): self
    {
        $this->juridicalLocation = $juridicalLocation;

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

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(?Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getTimeZone(): ?string
    {
        return $this->timeZone;
    }

    public function setTimeZone(?string $timeZone): self
    {
        $this->timeZone = $timeZone;

        return $this;
    }

    public function addLocationArea(LocationArea $locationArea): self
    {
        $this->locationAreas->add($locationArea);

        return $this;
    }

    public function removeLocationArea(LocationArea $locationArea): self
    {
        $this->locationAreas->removeElement($locationArea);

        return $this;
    }

    /**
     * @return Collection<LocationArea>
     */
    public function getLocationAreas(): Collection
    {
        return $this->locationAreas;
    }

    public function getNetwork(): ?Network
    {
        return $this->network;
    }

    public function setNetwork(?Network $network): self
    {
        $this->network = $network;

        return $this;
    }

    public function getErpSoftware(): ?string
    {
        return $this->erpSoftware;
    }

    public function setErpSoftware(?string $erpSoftware): self
    {
        $this->erpSoftware = $erpSoftware;

        return $this;
    }

    public function getManufacturedProductFamilies(): Collection
    {
        return $this->manufacturedProductFamilies;
    }

    public function addManufacturedProductFamily(ProductFamily $family): self
    {
        if (!$this->manufacturedProductFamilies->contains($family)) {
            $this->manufacturedProductFamilies->add($family);
            $family->addManufacturingFactory($this);
        }

        return $this;
    }

    public function removeManufacturedProductFamily(ProductFamily $family): self
    {
        if ($this->manufacturedProductFamilies->removeElement($family)) {
            $family->removeManufacturingFactory($this);
        }

        return $this;
    }

    public function getPublicWebsite(): ?string
    {
        return $this->publicWebsite;
    }

    public function setPublicWebsite(?string $publicWebsite): self
    {
        $this->publicWebsite = $publicWebsite;

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if (null === $this->currency && $this->capability->isSso()) {
            $context
                ->buildViolation('Currency is mandatory for SSO')
                ->atPath('currency')
                ->addViolation()
            ;
        }
        if (null === $this->erpSoftware && $this->capability->isSso()) {
            $context
                ->buildViolation('ERP Software is mandatory for SSO')
                ->atPath('erpSoftware')
                ->addViolation()
            ;
        }
    }
}
