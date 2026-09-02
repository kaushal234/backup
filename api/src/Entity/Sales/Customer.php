<?php

declare(strict_types=1);

namespace App\Entity\Sales;

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
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Controller\File\ZipController;
use App\Controller\Sales\CustomerController;
use App\Controller\Sales\CustomerFileUploadController;
use App\Controller\Sales\CustomerStatusController;
use App\Controller\Sales\CustomerTransferController;
use App\DataProcessor\Sales\CustomerWatchListDataProcessor;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Dto\Sales\CustomerWatchListInput;
use App\Entity\Address;
use App\Entity\Country;
use App\Entity\EquipmentRecord;
use App\Entity\Finance\CreditLimit;
use App\FileSystem\Zip\ZippableEntityInterface;
use App\Filter\Customer\CustomerActiveFilter;
use App\Filter\Customer\CustomerContactPointsBusinessUnitFilter;
use App\Filter\Customer\CustomerDemoUsedFilter;
use App\Filter\Customer\CustomerOpenSalesForecastFilter;
use App\Filter\Customer\CustomerWithCustomerRelationshipTeamFilter;
use App\Filter\SimpleSearchFilter;
use App\Serializer\Filter\ContextFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\CharToBoolean;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A TLD customer.
 */
#[ORM\Entity(repositoryClass: 'App\Repository\Sales\CustomerRepository')]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['customer', 'address', 'customer_type', 'people_public', 'expose_legacy', 'country', 'country_detail', 'country', 'subdivision:light']]),
        new GetCollection(
            uriTemplate: '/customers_export',
            normalizationContext: ['groups' => ['customer_export']],
            security: "is_granted('ACCESS_PEOPLE_900')",
            name: 'get_customer_export',
        ),
        new Put(security: "is_granted('FEATURE_CUSTOMER_EDIT') or is_granted('FEATURE_CUSTOMER_FINANCE_ADMIN')"),
        new Put(
            uriTemplate: '/customers/{id}/transfer',
            controller: CustomerTransferController::class,
            security: "is_granted('FEATURE_CUSTOMER_EDIT')",
            write: false,
            name: 'customer_transfer',
        ),
        new Put(
            uriTemplate: '/customers/{id}/status',
            controller: CustomerStatusController::class,
            openapi: new Operation(
                parameters: [
                    new Parameter(
                        name: 'status',
                        in: 'path',
                        description: 'Status',
                        required: true,
                        schema: ['type' => 'string'],
                    ),
                ],
            ),
            denormalizationContext: ['groups' => ['customer_write', 'customer_write_admin']],
            deserialize: false,
            validate: false,
            name: 'customer_status',
        ),
        new Put(
            uriTemplate: '/customers/{id}/watch_list',
            denormalizationContext: [],
            security: "is_granted('FEATURE_CUSTOMER_WATCH_LIST') or is_granted('MOO_ECUST')",
            input: CustomerWatchListInput::class,
            validate: false,
            name: 'customer_watch_list',
            processor: CustomerWatchListDataProcessor::class
        ),
        new Delete(security: "is_granted('FEATURE_CUSTOMER_DELETE')"),
        new Delete(
            uriTemplate: '/customers/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'customerFiles', fromClass: CustomerFile::class),
                'id' => new Link(fromClass: Customer::class),
            ],
            defaults: ['parentProperty' => 'customer', 'class' => CustomerFile::class],
            controller: DeleteController::class,
            security: "is_granted('CUSTOMER_FILES_DELETE_VOTER', object)",
            name: 'delete_customer_file',
        ),
        new Delete(
            uriTemplate: '/customers/{id}/logo/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: CustomerLogoFile::class),
                'id' => new Link(fromClass: Customer::class),
            ],
            defaults: ['parentProperty' => 'customer', 'class' => CustomerLogoFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_CUSTOMER_EDIT', object)",
            name: 'delete_customer_logo',
        ),
        new Get(),
        new Get(
            uriTemplate: '/customers/{id}/status',
            openapi: true,
            normalizationContext: ['groups' => ['customer:status']],
            security: "is_granted('AUTHORIZED_APPLICATION_FEATURE_CUSTOMER_STATUS')",
            name: 'get_customer_status',
        ),
        new Get(
            uriTemplate: '/customers/{id}/files',
            formats: ['zip' => ['application/zip']],
            controller: ZipController::class,
            security: "is_granted('CUSTOMER_FILES_READ_VOTER', object)",
            name: 'get_customer_files',
        ),
        new Get(
            uriTemplate: '/customers/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'customerFiles', fromClass: CustomerFile::class),
                'id' => new Link(fromClass: Customer::class),
            ],
            defaults: ['parentProperty' => 'customer', 'class' => CustomerFile::class],
            controller: DownloadController::class,
            security: "is_granted('CUSTOMER_FILES_READ_VOTER', object)",
            name: 'download_customer_file',
        ),
        new Get(
            uriTemplate: '/customers/{id}/hierarchy',
            controller: CustomerController::class,
            normalizationContext: ['groups' => ['customer', 'customer_detail', 'address', 'customer_type', 'people_public', 'expose_legacy', 'location_public', 'disable_max_depth', 'credit_limit', 'currency']],
            name: 'customer_hierarchy',
        ),
        new Post(
            denormalizationContext: ['groups' => ['customer_write', 'customer_create', 'address_write', 'country_write']],
            security: "is_granted('FEATURE_CUSTOMER_CREATE')",
        ),
        new Post(
            uriTemplate: '/customers/{id}/logo',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getLogo', 'class' => CustomerLogoFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_CUSTOMER_EDIT')",
            deserialize: false,
            name: 'upload_customer_logo',
        ),
        new Post(
            uriTemplate: '/customers/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getCustomerFiles', 'class' => CustomerFile::class],
            controller: CustomerFileUploadController::class,
            security: "is_granted('CUSTOMER_FILES_UPLOAD_VOTER', object)",
            deserialize: false,
            name: 'upload_customer_file',
        ),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['customer', 'customer_detail', 'address', 'customer_type', 'people_public', 'file', 'expose_legacy', 'country', 'location_public', 'credit_limit', 'currency', 'subdivision:light']],
    denormalizationContext: ['groups' => ['customer_write', 'address_write', 'country_write']],
)]
#[ORM\Table(name: 'customers')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC', 'createdAt' => 'DESC', 'id' => 'DESC'])]
#[ApiFilter(BooleanFilter::class, properties: ['hidden', 'watchList'])]
#[ApiFilter(CustomerOpenSalesForecastFilter::class)]
#[ApiFilter(CustomerActiveFilter::class)]
#[ApiFilter(CustomerContactPointsBusinessUnitFilter::class)]
#[ApiFilter(ExistsFilter::class, properties: ['mainSalesRepresentative', 'inforLnBusinessPartnerCodes'])]
#[ApiFilter(CustomerDemoUsedFilter::class)]
#[ApiFilter(CustomerWithCustomerRelationshipTeamFilter::class)]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
#[ApiFilter(SearchFilter::class, properties: [
    'mainSalesRepresentative.asm' => 'exact',
    'mainSalesRepresentative.asm.businessUnit.region' => 'exact',
    'legacyId' => 'exact',
    'customerTypes' => 'exact',
    'status' => 'exact',
    'country' => 'exact',
    'crt.partsLocation' => 'exact',
    'crt.serviceLocation' => 'exact',
    'crt.erpLocation' => 'exact',
    'crt.acls.extranetUserGroup' => 'exact',
    'equipmentRecordsAsBuyer.product.family.productType',
    'equipmentRecordsAsUser.product.family.productType',
    'inforLnBusinessPartnerCodes' => 'partial'])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['customer_list', 'customer_crt', 'er_buyer_product_count', 'er_user_product_count', 'er_buyer_count', 'er_user_count']])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups', 'overrideDefaultGroups' => false, 'whitelist' => ['customer_crt', 'location_public', 'customer:watch', 'division', 'subdivision']])]
#[ApiFilter(ContextFilter::class)]
#[App\Loggable]
#[Legacy\Synchronize(table: 'customers')]
#[Gedmo\SoftDeleteable]
class Customer implements \Stringable, LegacyIdInterface, ZippableEntityInterface
{
    use LegacyIdentifierTrait;

    /**
     * @var string
     */
    final public const PENDING = 'PENDING';
    /**
     * @var string
     */
    final public const PENDING_RE_APPROVAL = 'PENDING RE-APPROVAL';
    /**
     * @var string
     */
    final public const APPROVED = 'APPROVED';
    /**
     * @var string
     */
    final public const NOT_APPROVED = 'NOT APPROVED';
    /**
     * @var string
     */
    final public const NOT_ACTIVE = 'NOT ACTIVE';

    /**
     * @var string
     */
    final public const CUSTOMER_DEMO = '**DEMO**';
    /**
     * @var string
     */
    final public const CUSTOMER_PROTO = '**PROTO**';
    /**
     * @var string
     */
    final public const CUSTOMER_STOCK = '**STOCK**';

    /**
     * @var array
     */
    final public const ACTIVE_STATUSES = [self::APPROVED, self::PENDING, self::PENDING_RE_APPROVAL];
    /**
     * @var string
     */
    final public const CUSTOMER_AVAILABLE_FOR_SALE = '**AVAILABLE FOR SALE**';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['customer', 'customer_export', 'customer_light'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer', inversedBy: 'childrenCustomers')]
    #[ORM\JoinColumn(nullable: true)]
    #[Transferable(manager: 'manager.customer')]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Groups(['customer_detail', 'customer_write'])]
    private ?Customer $parentCustomer = null;

    /**
     * @var Collection<Customer>
     */
    #[ORM\OneToMany(mappedBy: 'parentCustomer', targetEntity: 'App\Entity\Sales\Customer')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['customer_detail', 'customer_write'])]
    #[MaxDepth(1)]
    private Collection $childrenCustomers;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column(name: 'name', type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['customer', 'customer_list', 'customer_type_detail', 'customer_relationship_team', 'customer_relationship_team_detail', 'customer_write', 'equipment_record', 'equipment_record_detail', 'maintenance_contract', 'maintenance_contract_detail', 'er_user_count', 'er_buyer_count', 'er_buyer_product_count', 'er_user_product_count', 'equipment_list_user', 'equipment_list_buyer', 'demo_list', 'customer_export', 'customer_public', 'sfr_export', 'equipment_record_manuals', 'customer_light'])]
    #[Legacy\Column(column: 'customer_name', transformer: Utf8ToHtmlEntities::class)]
    private string $name;

    #[ApiProperty(iris: ['https://schema.org/PostalAddress'])]
    #[ORM\Embedded(class: '\App\Entity\Address')]
    #[Assert\Valid]
    #[Groups(['customer', 'customer_write', 'customer_light'])]
    #[Legacy\Column(column: 'customer_address', options: ['embeddedFields' => ['address.street1', 'address.street2', 'address.town', 'address.postalCode', 'address.city', 'address.state']])]
    private ?Address $address = null;

    #[ApiProperty(iris: ['https://schema.org/addressCountry'])]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Country')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['customer', 'customer_public', 'customer_write', 'customer_light'])]
    #[Legacy\Column(column: 'ctry_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?Country $country = null;

    #[ORM\Column(name: 'legacy_address', type: 'text', length: 65000)]
    #[Groups(['customer_detail', 'customer_write'])]
    private string $legacyAddress = '';

    #[ApiProperty(iris: ['https://schema.org/telephone'])]
    #[ORM\Column(name: 'phone', type: 'string', length: 60, nullable: true)]
    #[Groups(['customer_detail', 'customer_write', 'customer_light'])]
    #[Legacy\Column(column: 'customer_tel')]
    private ?string $phone = null;

    #[ApiProperty(iris: ['https://schema.org/telephone'])]
    #[ORM\Column(name: 'fax', type: 'string', length: 60, nullable: true)]
    #[Groups(['customer_detail', 'customer_write', 'customer_light'])]
    #[Legacy\Column(column: 'customer_fax')]
    private ?string $fax = null;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(name: 'hidden', type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['customer_detail', 'customer_write_admin'])]
    #[Legacy\Column(column: 'hidden')]
    private bool $hidden = false;

    #[ApiProperty(iris: ['https://schema.org/url'])]
    #[ORM\Column(name: 'url', type: 'string', length: 255, nullable: true)]
    #[Assert\Url(requireTld: true)]
    #[Groups(['customer_detail', 'customer_write'])]
    #[Legacy\Column(column: 'url')]
    private ?string $url = null;

    #[ORM\OneToOne(targetEntity: 'App\Entity\Sales\MainSalesRepresentative', inversedBy: 'customer', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['customer', 'customer_write', 'customer_campaign'])]
    #[Legacy\Column(column: 'asm_id', transformer: ObjectToProperty::class, options: ['property' => 'asm.legacyId'])]
    private ?MainSalesRepresentative $mainSalesRepresentative = null;

    /**
     * @var Collection<SecondarySalesRepresentative>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Sales\SecondarySalesRepresentative', mappedBy: 'customer', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['customer_detail', 'customer_write'])]
    private Collection $secondarySalesRepresentatives;

    /**
     * @var Collection<CustomerLogoFile>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Sales\CustomerLogoFile', mappedBy: 'customer', cascade: ['persist'], orphanRemoval: true)]
    private Collection $files;

    /**
     * @var Collection<CustomerRelationshipTeam>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Sales\CustomerRelationshipTeam', mappedBy: 'customer')]
    #[Groups(['customer_crt'])]
    private Collection $crt;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'deleted_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<CustomerFile>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Sales\CustomerFile', mappedBy: 'customer', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['customer_detail'])]
    private Collection $customerFiles;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Choice(choices: [self::PENDING, self::PENDING_RE_APPROVAL, self::APPROVED, self::NOT_APPROVED, self::NOT_ACTIVE])]
    #[Groups(['customer', 'customer_list', 'customer:status', 'customer_create', 'customer_export'])]
    #[Legacy\Column(column: 'approved', transformer: CharToBoolean::class, options: ['values' => [self::PENDING => false, self::PENDING_RE_APPROVAL => true, self::APPROVED => true, self::NOT_APPROVED => false, self::NOT_ACTIVE => false]])]
    private string $status = self::PENDING;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['customer_export'])]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: [self::APPROVED])]
    private ?\DateTimeInterface $validatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $reApprovedAt = null;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(type: 'boolean')]
    #[Groups(['customer_detail', 'customer:watch'])]
    private bool $watchList = false;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['customer_detail', 'customer:watch'])]
    private ?string $watchListReason = null;

    /**
     * @var Collection<EquipmentRecord>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\EquipmentRecord', mappedBy: 'buyer')]
    private Collection $equipmentRecordsAsBuyer;

    /**
     * @var Collection<EquipmentRecord>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\EquipmentRecord', mappedBy: 'endUser')]
    private Collection $equipmentRecordsAsUser;

    /**
     * @var Collection<EquipmentRecord>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\EquipmentRecord', mappedBy: 'maintainer')]
    private Collection $equipmentRecordsAsMaintainer;

    /**
     * @var Collection<CustomerErpReference>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Sales\CustomerErpReference', mappedBy: 'customer', orphanRemoval: true)]
    private Collection $customerErpReferences;

    /**
     * @var Collection<CreditLimit>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Finance\CreditLimit', mappedBy: 'customer', cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Valid]
    #[Groups(['customer_detail', 'customer:finance_write'])]
    private Collection $creditLimits;

    /**
     * @var Collection<CustomerType>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\CustomerType', inversedBy: 'customers')]
    #[Groups(['customer', 'customer_write', 'sales_order:detail'])]
    private Collection $customerTypes;

    #[ORM\Column(nullable: true)]
    #[Groups(['customer_detail', 'customer_write'])]
    #[Legacy\Column(column: 'customer_em_jira_key')]
    private ?string $easymileJiraProjectKey = null;

    #[ORM\Column(type: 'simple_array', length: 255, nullable: true)]
    #[Groups(['customer_detail', 'customer_write'])]
    private array $inforLnBusinessPartnerCodes = [];

    public function __construct()
    {
        $this->childrenCustomers = new ArrayCollection();
        $this->crt = new ArrayCollection();
        $this->customerFiles = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->equipmentRecordsAsBuyer = new ArrayCollection();
        $this->equipmentRecordsAsUser = new ArrayCollection();
        $this->equipmentRecordsAsMaintainer = new ArrayCollection();
        $this->customerErpReferences = new ArrayCollection();
        $this->creditLimits = new ArrayCollection();
        $this->customerTypes = new ArrayCollection();
        $this->secondarySalesRepresentatives = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setParentCustomer(?self $parentCustomer = null): self
    {
        $this->parentCustomer = $parentCustomer;

        return $this;
    }

    public function getParentCustomer(): ?self
    {
        return $this->parentCustomer;
    }

    public function addChildrenCustomer(self $childrenCustomer): self
    {
        $this->childrenCustomers->add($childrenCustomer);
        $childrenCustomer->setParentCustomer($this);

        return $this;
    }

    public function removeChildrenCustomer(self $childrenCustomer): self
    {
        $this->childrenCustomers->removeElement($childrenCustomer);
        $childrenCustomer->setParentCustomer();

        return $this;
    }

    /**
     * @return Collection<Customer>
     */
    public function getChildrenCustomers(): Collection
    {
        return $this->childrenCustomers;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address = null): self
    {
        $this->address = $address;

        return $this;
    }

    public function getLegacyAddress(): ?string
    {
        return $this->legacyAddress;
    }

    public function setLegacyAddress(string $address = ''): self
    {
        $this->legacyAddress = $address;

        return $this;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setUrl(?string $url = null): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setPhone(?string $phone = null): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setFax(?string $fax = null): self
    {
        $this->fax = $fax;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return Collection<CustomerRelationshipTeam>
     */
    public function getCrt(): Collection
    {
        return $this->crt;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection<CustomerFile>
     */
    public function getCustomerFiles(): Collection
    {
        return $this->customerFiles;
    }

    public function addCustomerFile(CustomerFile $customerFile): self
    {
        $customerFile->setCustomer($this);
        $this->customerFiles->add($customerFile);

        return $this;
    }

    public function removeCustomerFile(CustomerFile $customerFile): self
    {
        $this->customerFiles->removeElement($customerFile);

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeInterface
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeInterface $validatedAt): self
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    /**
     * @return Collection<EquipmentRecord>
     */
    public function getEquipmentRecordsAsBuyer(): Collection
    {
        return $this->equipmentRecordsAsBuyer;
    }

    /**
     * @return Collection<EquipmentRecord>
     */
    public function getEquipmentRecordsAsUser(): Collection
    {
        return $this->equipmentRecordsAsUser;
    }

    /**
     * @return Collection<EquipmentRecord>
     */
    public function getEquipmentRecordsAsMaintainer(): Collection
    {
        return $this->equipmentRecordsAsMaintainer;
    }

    #[Groups('customer_export')]
    #[SerializedName('country')]
    public function getCountryForExport(): string
    {
        return null !== $this->getCountry() ? $this->getCountry()->getName() : '';
    }

    #[Groups('customer_export')]
    #[SerializedName('hidden')]
    public function getHiddenForExport(): string
    {
        return $this->hidden ? 'yes' : 'no';
    }

    #[Groups(['customer_detail'])]
    public function getLogo(): ?CustomerLogoFile
    {
        if (0 === $this->files->count()) {
            return null;
        }

        return $this->files->first();
    }

    public function setLogo(?CustomerLogoFile $file): self
    {
        if (null === $file) {
            $this->files = new ArrayCollection();

            return $this;
        }

        return $this->addFile($file);
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(CustomerLogoFile $file): self
    {
        $file->setCustomer($this);
        $this->files->add($file);

        return $this;
    }

    public function removeFile(CustomerLogoFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }

    public function isWatchList(): bool
    {
        return $this->watchList;
    }

    public function setWatchList(bool $watchList): self
    {
        $this->watchList = $watchList;

        return $this;
    }

    public function getWatchListReason(): ?string
    {
        return $this->watchListReason;
    }

    public function setWatchListReason(?string $watchListReason): self
    {
        $this->watchListReason = $watchListReason;

        return $this;
    }

    /**
     * @return Collection<CustomerErpReference>
     */
    public function getCustomerErpReferences(): Collection
    {
        return $this->customerErpReferences;
    }

    /**
     * @return Collection<CreditLimit>
     */
    public function getCreditLimits(): Collection
    {
        return $this->creditLimits;
    }

    public function addCreditLimit(CreditLimit $creditLimit): self
    {
        if (!$this->creditLimits->contains($creditLimit)) {
            $creditLimit->customer = $this;
            $this->creditLimits->add($creditLimit);
        }

        return $this;
    }

    public function removeCreditLimit(CreditLimit $creditLimit): self
    {
        if ($this->creditLimits->contains($creditLimit)) {
            $this->creditLimits->removeElement($creditLimit);
        }

        return $this;
    }

    /**
     * @return Collection<CustomerType>
     */
    public function getCustomerTypes(): Collection
    {
        return $this->customerTypes;
    }

    public function addCustomerType(CustomerType $customerType): self
    {
        $this->customerTypes->add($customerType);

        return $this;
    }

    public function removeCustomerType(CustomerType $customerType): self
    {
        $this->customerTypes->removeElement($customerType);

        return $this;
    }

    /**
     * @return Collection<SecondarySalesRepresentative>
     */
    public function getSecondarySalesRepresentatives(): Collection
    {
        return $this->secondarySalesRepresentatives;
    }

    public function addSecondarySalesRepresentative(SecondarySalesRepresentative $secondarySalesRepresentative): self
    {
        $this->secondarySalesRepresentatives->add($secondarySalesRepresentative);
        $secondarySalesRepresentative->customer = $this;

        return $this;
    }

    public function removeSecondarySalesRepresentative(SecondarySalesRepresentative $secondarySalesRepresentative): self
    {
        $this->secondarySalesRepresentatives->removeElement($secondarySalesRepresentative);

        return $this;
    }

    public function getMainSalesRepresentative(): ?MainSalesRepresentative
    {
        return $this->mainSalesRepresentative;
    }

    public function setMainSalesRepresentative(?MainSalesRepresentative $mainSalesRepresentative): self
    {
        $this->mainSalesRepresentative = $mainSalesRepresentative;
        if (null !== $mainSalesRepresentative) {
            $mainSalesRepresentative->customer = $this;
        }

        return $this;
    }

    public function getReApprovedAt(): ?\DateTimeInterface
    {
        return $this->reApprovedAt;
    }

    public function setReApprovedAt(?\DateTimeInterface $reApprovedAt): self
    {
        $this->reApprovedAt = $reApprovedAt;

        return $this;
    }

    public function getEasymileJiraProjectKey(): ?string
    {
        return $this->easymileJiraProjectKey;
    }

    public function setEasymileJiraProjectKey(?string $easymileJiraProjectKey): self
    {
        $this->easymileJiraProjectKey = $easymileJiraProjectKey;

        return $this;
    }

    public function setInforLnBusinessPartnerCodes(array $inforLnBusinessPartnerCodes): self
    {
        $this->inforLnBusinessPartnerCodes = $inforLnBusinessPartnerCodes;

        return $this;
    }

    public function getInforLnBusinessPartnerCodes(): array
    {
        return $this->inforLnBusinessPartnerCodes;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if ($this === $this->parentCustomer) {
            $context
                ->buildViolation('A customer cannot be its own parent.')
                ->atPath('parentCustomer')
                ->addViolation()
            ;
        }

        if (null !== $found = $this->hasCircularReference($this, 1)) {
            $context
                ->buildViolation(\sprintf('%s cannot be the parent of because it is in the hierarchy of the children of this customer (child of %s).', $this->parentCustomer->getName(), $found->getName()))
                ->atPath('parentCustomer')
                ->addViolation()
            ;
        }
    }

    /**
     * @return Collection<CustomerFile>
     */
    public function getZippableFiles(): Collection
    {
        return $this->getCustomerFiles();
    }

    public function isEquipmentRecordBuyer(EquipmentRecord $equipmentRecord): bool
    {
        return $this->getEquipmentRecordsAsBuyer()->contains($equipmentRecord);
    }

    public function isEquipmentRecordUser(EquipmentRecord $equipmentRecord): bool
    {
        return $this->getEquipmentRecordsAsUser()->contains($equipmentRecord);
    }

    private function hasCircularReference(self $customer, int $level): ?self
    {
        if ($level > 5) {
            return null;
        }

        $children = $customer->getChildrenCustomers();

        if ($children->contains($this->parentCustomer)) {
            return $customer;
        }

        foreach ($children as $child) {
            if (null !== $found = $this->hasCircularReference($child, $level + 1)) {
                return $found;
            }
        }

        return null;
    }
}
