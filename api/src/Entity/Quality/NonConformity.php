<?php

declare(strict_types=1);

namespace App\Entity\Quality;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Controller\File\ZipController;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Finance\Currency;
use App\Entity\Parts\NonConformityPart;
use App\Entity\Purchasing\NCRVendorWarrantyClaim;
use App\Entity\Sales\Product;
use App\Entity\SupplierEntityInterface;
use App\FileSystem\Zip\ZippableEntityInterface;
use App\Filter\ColumnsFilter;
use App\Filter\SimpleSearchFilter;
use App\ION\Validator\Constraints\MasterData\BusinessPartners\BusinessPartnerSupplier;
use App\Repository\Quality\NonConformityRepository;
use App\SageParts\P21\Validator\Constraints\SagePartsSupplier;
use App\Validator\Constraints\LocationOr as ValidOrLocation;
use App\Validator\Constraints\OpenTasks;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: NonConformityRepository::class)]

#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => ['non_conformity', 'people_public', 'location_public', 'process', 'non_conformity:responsible', 'responsible']],
        ),
        new Delete(security: "is_granted('FEATURE_NON_CONFORMITY_DELETE')"),
        new Delete(
            uriTemplate: '/non_conformities/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: NonConformityFile::class),
                'id' => new Link(fromClass: NonConformity::class),
            ],
            defaults: ['parentProperty' => 'nonConformity', 'class' => NonConformityFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_NON_CONFORMITY_FILE_DELETE')",
            name: 'delete_non_conformity_file',
        ),
        new Delete(
            uriTemplate: '/non_conformities/{id}/main_file/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'mainFiles', fromClass: NonConformityMainFile::class),
                'id' => new Link(fromClass: NonConformity::class),
            ],
            defaults: ['parentProperty' => 'nonConformity', 'class' => NonConformityMainFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_NON_CONFORMITY_EDIT')",
            name: 'delete_non_conformity_main_file',
        ),
        new Get(),
        new Get(
            uriTemplate: '/non_conformities/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: NonConformityFile::class),
                'id' => new Link(fromClass: NonConformity::class),
            ],
            defaults: ['parentProperty' => 'nonConformity', 'class' => NonConformityFile::class],
            controller: DownloadController::class,
            name: 'download_non_conformity_file',
        ),
        new Get(
            uriTemplate: '/non_conformities/{id}/files',
            formats: ['zip' => ['application/zip']],
            controller: ZipController::class,
            name: 'zip_non_conformity_files',
        ),
        new Get(
            uriTemplate: '/non_conformities/{id}/main_file/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'mainFiles', fromClass: NonConformityMainFile::class),
                'id' => new Link(fromClass: NonConformity::class),
            ],
            defaults: ['parentProperty' => 'nonConformity', 'class' => NonConformityMainFile::class],
            controller: DownloadController::class,
            name: 'download_main_non_conformity_file',
        ),
        new Put(security: "is_granted('NON_CONFORMITY_EDIT_VOTER', object)"),
        new Post(
            uriTemplate: '/non_conformities/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => NonConformityFile::class],
            controller: UploadController::class,
            deserialize: false,
            name: 'upload_non_conformity_file',
        ),
        new Post(
            uriTemplate: '/non_conformities/{id}/main_file',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getMainFiles', 'class' => NonConformityMainFile::class],
            controller: UploadController::class,
            deserialize: false,
            name: 'upload_main_non_conformity_file',
        ),
        new Post(),
    ],
    routePrefix: 'quality',
    normalizationContext: [
        'groups' => ['non_conformity', 'non_conformity:detail', 'people_public', 'location_public', 'currency', 'supplier:list', 'file', 'catalogue_public', 'process', 'part', 'responsible', 'equipment_list', 'crab:list'],
    ],
    denormalizationContext: [
        'groups' => ['non_conformity:write_crab', 'non_conformity:write_product', 'non_conformity:write_parts', 'part:admin'],
    ],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
)]
#[ORM\Table]
#[ApiFilter(OrderFilter::class, properties: ['id', 'reportedBy.lastname', 'solution'])]
#[ApiFilter(BooleanFilter::class, properties: ['safety', 'environmentalIssue', 'rush'])]
#[ApiFilter(SearchFilter::class, properties: ['id', 'status', 'location', 'reportedBy', 'reportedBy.department', 'processes', 'responsibles', 'supplierNumber', 'supplierName' => 'partial', 'parts.partNumber', 'parts.serialNumber', 'products', 'product.family.productType', 'equipmentRecords.serialNumber', 'parts.referenceNumber' => 'partial'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['problem' => 'partial', 'solution' => 'partial', 'costBreakdown' => 'partial', 'investigation' => 'partial', 'id' => 'partial'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt' => 'exact'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'whitelist' => ['non_conformity:legacy', 'expose_legacy', 'file', 'non_conformity:files']])]
#[ApiFilter(ColumnsFilter::class)]
#[App\Loggable]
#[OpenTasks(statuses: self::CLOSED_STATUSES, module: 'NCR')]
class NonConformity implements SupplierEntityInterface, ZippableEntityInterface
{
    /** @var string */
    final public const TLD = 'TLD';

    /** @var string */
    final public const SUPPLIER = 'Supplier';

    /** @var string */
    final public const CUSTOMER = 'Customer';

    /** @var string */
    final public const OTHER = 'Other';

    /** @var string */
    final public const PENDING = 'PENDING';

    /** @var string */
    final public const IN_PROGRESS = 'IN PROGRESS';

    /** @var string */
    final public const SUSPENDED = 'SUSPENDED';

    /** @var string */
    final public const REJECTED = 'REJECTED';

    /** @var string */
    final public const CLOSED = 'CLOSED';

    /** @var string */
    final public const HYDRAULIC = 'Hydraulic';

    /** @var string */
    final public const ELECTRICAL = 'Electrical';

    /** @var string */
    final public const MECHANICAL = 'Mechanical';

    /** @var string */
    final public const WELDMENT = 'Weldment';

    /** @var string */
    final public const PAINT = 'Paint';

    /** @var string */
    final public const SURFACE_COATING = 'Surface coating';

    /** @var string */
    final public const ENGINE_POWER_TRAIN = 'Engine & power train system';

    /** @var string */
    final public const ADMINISTRATION = 'Administration';

    /** @var string */
    final public const OTHER_MODEL = 'Other / Discontinued';

    /** @var string */
    final public const ENVIRONMENTAL_ISSUE = 'NCR Environmental Issue';

    final public const CLOSED_STATUSES = [self::CLOSED, self::REJECTED];

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['non_conformity', 'non_conformity:edit', 'non_conformity:create'])]
    #[ValidOrLocation(sso: true, factory: true, sparePartsHub: true)]
    public Location $location;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['non_conformity'])]
    #[Gedmo\Timestampable(on: 'create')]
    public \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: self::CLOSED_STATUSES)]
    public ?\DateTime $closedAt = null;

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: [self::PENDING, self::IN_PROGRESS, self::SUSPENDED, self::REJECTED, self::CLOSED])]
    #[Groups(['non_conformity', 'non_conformity:status'])]
    public string $status = self::PENDING;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['non_conformity:detail', 'non_conformity:edit', 'non_conformity:create'])]
    public ?int $hours;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['non_conformity', 'non_conformity:edit', 'non_conformity:create'])]
    public ?People $reportedBy;

    #[ORM\Column(type: 'text')]
    #[Groups(['non_conformity', 'non_conformity:detail', 'non_conformity:edit', 'non_conformity:create', 'non_conformity:light', 'non_conformity:partial_edit'])]
    public string $problem;

    #[ORM\Column(type: 'string')]
    #[Groups(['non_conformity', 'non_conformity:edit', 'non_conformity:create', 'non_conformity:partial_edit'])]
    public string $shortDescription;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['non_conformity', 'non_conformity:edit'])]
    public ?string $solution = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public ?string $purchaseOrderNumber = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['non_conformity', 'non_conformity:detail', 'non_conformity:edit', 'non_conformity:create'])]
    public bool $rush = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public bool $chargeVendor = false;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Choice(choices: [self::ELECTRICAL, self::MECHANICAL, self::WELDMENT, self::ENGINE_POWER_TRAIN, self::ADMINISTRATION, self::HYDRAULIC, self::PAINT, self::SURFACE_COATING])]
    #[Groups(['non_conformity', 'non_conformity:edit'])]
    public ?string $failureType = null;

    #[ORM\Column(type: 'string')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit', 'non_conformity:create'])]
    public string $iFactor;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['non_conformity:detail', 'non_conformity:edit', 'non_conformity:create', 'non_conformity:partial_edit'])]
    public ?string $investigation = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public bool $scrap = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public bool $rework = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public bool $firstArticleInspection = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public bool $useAsIs = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public bool $derogation = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public bool $returnVendor = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public bool $chargeVendorForRepair = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public bool $supplierCorrectiveActionRequest = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public bool $internalCorrectiveActionRequest = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public bool $other = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public bool $containment = false;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public ?string $actionComment = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public ?People $repairApprover = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public ?\DateTimeInterface $repairApprovalDate = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Finance\Currency')]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public ?Currency $currency = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public ?string $costBreakdown = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public ?float $cost = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public ?string $workOrderReference = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public ?float $nonQualityCost = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    public ?string $invoiceNumber = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['non_conformity:detail', 'non_conformity:write_product'])]
    public bool $environmentalIssue = false;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    #[Groups(['non_conformity:detail', 'non_conformity:write_product'])]
    public bool $safety = false;

    /**
     * @var Collection<Crab>
     */
    #[ORM\OneToMany(targetEntity: Crab::class, mappedBy: 'nonConformity', cascade: ['persist', 'remove'])]
    #[Groups(['non_conformity:detail', 'non_conformity:write_crab'])]
    private Collection $crabs;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['non_conformity:detail'])]
    private ?string $supplierName = null;

    #[Assert\When(
        expression: 'this.getLocation().getErpSoftware() === "LN"',
        constraints: new BusinessPartnerSupplier()
    )]
    #[Assert\When(
        expression: 'this.getLocation().getErpSoftware() === "P21"',
        constraints: new SagePartsSupplier()
    )]
    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['non_conformity:detail', 'non_conformity:edit'])]
    private ?string $supplierNumber = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $supplierErp = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $baanSupplierNumber = null;

    /**
     * @var Collection<Process>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Quality\Process')]
    #[Groups(['non_conformity', 'non_conformity:edit'])]
    #[Assert\Count(max: 2)]
    private Collection $processes;

    /**
     * @var Collection<Responsible>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Quality\Responsible')]
    #[Groups(['non_conformity:responsible', 'non_conformity:detail', 'non_conformity:edit'])]
    #[Assert\Count(max: 2)]
    private Collection $responsibles;

    /**
     * @var Collection<NonConformityPart>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Parts\NonConformityPart', mappedBy: 'nonConformity', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['non_conformity:detail', 'non_conformity:write_parts'])]
    private Collection $parts;

    /**
     * @var Collection<NonConformityFile>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Quality\NonConformityFile', mappedBy: 'nonConformity', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['non_conformity:detail', 'non_conformity:files'])]
    private Collection $files;

    /**
     * @var Collection<NonConformityMainFile>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Quality\NonConformityMainFile', mappedBy: 'nonConformity', cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(max: 1)]
    private Collection $mainFiles;

    /**
     * @var Collection<Product>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\Product')]
    #[Groups(['non_conformity:detail', 'non_conformity:write_product'])]
    private Collection $products;

    /**
     * @var Collection<NCRVendorWarrantyClaim>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Purchasing\NCRVendorWarrantyClaim', mappedBy: 'nonConformity', orphanRemoval: true)]
    private Collection $vendorWarrantyClaims;

    /**
     * @var Collection<EquipmentRecord>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\EquipmentRecord')]
    #[Groups(['non_conformity:detail', 'non_conformity:create', 'non_conformity:edit'])]
    private Collection $equipmentRecords;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['non_conformity', 'non_conformity:light'])]
    private int $id;

    public function __construct()
    {
        $this->processes = new ArrayCollection();
        $this->responsibles = new ArrayCollection();
        $this->parts = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->mainFiles = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->vendorWarrantyClaims = new ArrayCollection();
        $this->equipmentRecords = new ArrayCollection();
        $this->crabs = new ArrayCollection();
    }

    public function getSupplierName(): ?string
    {
        return $this->supplierName;
    }

    public function setSupplierName(?string $name): self
    {
        $this->supplierName = $name;

        return $this;
    }

    public function getSupplierNumber(): ?string
    {
        return $this->supplierNumber;
    }

    public function setSupplierNumber(?string $supplierNumber): self
    {
        $this->supplierNumber = $supplierNumber;

        return $this;
    }

    public function getSupplierErp(): ?int
    {
        return $this->supplierErp;
    }

    public function setSupplierErp(?int $supplierErp): void
    {
        $this->supplierErp = $supplierErp;
    }

    /**
     * @return Collection<Crab>
     */
    public function getCrabs(): Collection
    {
        return $this->crabs;
    }

    public function addCrab(Crab $crab): self
    {
        if (!$this->crabs->contains($crab)) {
            $this->crabs->add($crab);
            $crab->nonConformity = $this;
        }

        return $this;
    }

    public function removeCrab(Crab $crab): self
    {
        if ($this->crabs->contains($crab)) {
            $this->crabs->removeElement($crab);
            $crab->nonConformity = null;
        }

        return $this;
    }

    /**
     * @return Collection<NonConformityPart>
     */
    public function getParts()
    {
        return $this->parts;
    }

    public function addPart(NonConformityPart $part): self
    {
        if (!$this->parts->contains($part)) {
            $this->parts->add($part);
            $part->nonConformity = $this;
        }

        return $this;
    }

    public function removePart(NonConformityPart $part): self
    {
        if ($this->parts->contains($part)) {
            $this->parts->removeElement($part);
        }

        return $this;
    }

    /**
     * @return Collection<Process>
     */
    public function getProcesses()
    {
        return $this->processes;
    }

    public function addProcess(Process $process): self
    {
        if (!$this->processes->contains($process)) {
            $this->processes->add($process);
        }

        return $this;
    }

    public function removeProcess(Process $process): self
    {
        if ($this->processes->contains($process)) {
            $this->processes->removeElement($process);
        }

        return $this;
    }

    /**
     * @return Collection<Responsible>
     */
    public function getResponsibles()
    {
        return $this->responsibles;
    }

    public function addResponsible(Responsible $responsibles): self
    {
        if (!$this->responsibles->contains($responsibles)) {
            $this->responsibles->add($responsibles);
        }

        return $this;
    }

    public function removeResponsible(Responsible $responsibles): self
    {
        if ($this->responsibles->contains($responsibles)) {
            $this->responsibles->removeElement($responsibles);
        }

        return $this;
    }

    /**
     * @return Collection<NonConformityFile>
     */
    public function getFiles()
    {
        return $this->files;
    }

    public function addFile(NonConformityFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setNonConformity($this);
        }

        return $this;
    }

    public function removeFile(NonConformityFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }

        return $this;
    }

    /**
     * @return Collection<NonConformityMainFile>
     */
    public function getMainFiles()
    {
        return $this->mainFiles;
    }

    public function addMainFile(NonConformityMainFile $mainFile): self
    {
        if (!$this->mainFiles->contains($mainFile)) {
            $this->mainFiles->add($mainFile);
            $mainFile->setNonConformity($this);
        }

        return $this;
    }

    public function removeMainFile(NonConformityMainFile $mainFile): self
    {
        if ($this->mainFiles->contains($mainFile)) {
            $this->mainFiles->removeElement($mainFile);
        }

        return $this;
    }

    #[Groups(['non_conformity:detail', 'non_conformity:main_file'])]
    public function getMainFile(): ?NonConformityMainFile
    {
        if (0 === $this->mainFiles->count()) {
            return null;
        }

        return $this->mainFiles->first();
    }

    public function setMainFile(?NonConformityMainFile $mainFile): self
    {
        if (null === $mainFile) {
            $this->mainFiles = new ArrayCollection();

            return $this;
        }

        return $this->addMainFile($mainFile);
    }

    /**
     * @return Collection<Product>
     */
    public function getProducts()
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
        }

        return $this;
    }

    /**
     * @return Collection<NCRVendorWarrantyClaim>
     */
    public function getVendorWarrantyClaims(): Collection
    {
        return $this->vendorWarrantyClaims;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<EquipmentRecord>
     */
    public function getEquipmentRecords(): Collection
    {
        return $this->equipmentRecords;
    }

    public function addEquipmentRecord(EquipmentRecord $equipmentRecord): self
    {
        if (!$this->equipmentRecords->contains($equipmentRecord)) {
            $this->equipmentRecords->add($equipmentRecord);
        }

        return $this;
    }

    public function removeEquipmentRecord(EquipmentRecord $equipmentRecord): self
    {
        if ($this->equipmentRecords->contains($equipmentRecord)) {
            $this->equipmentRecords->removeElement($equipmentRecord);
        }

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if (self::CLOSED === $this->status && ($this->responsibles->isEmpty() || $this->processes->isEmpty())) {
            $context->buildViolation('NCR can\'t be closed if responsible and process are not set.')
                ->atPath('status')
                ->addViolation();
        }

        if (!$this->products->isEmpty() && $this->environmentalIssue) {
            foreach (['products', 'environmentalIssue'] as $field) {
                $context->buildViolation('You can\'t select products and environmental issue at the same time.')
                    ->atPath($field)
                    ->addViolation();
            }
        }
    }

    /**
     * @return Collection<NonConformityFile>
     */
    public function getZippableFiles(): Collection
    {
        return $this->getFiles();
    }

    public function getBusinessPartnerCode(): string
    {
        return $this->getSupplierNumber();
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }
}
