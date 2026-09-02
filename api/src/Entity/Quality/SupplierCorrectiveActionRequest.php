<?php

declare(strict_types=1);

namespace App\Entity\Quality;

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
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Activity\Comment;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Parts\SupplierCorrectiveActionRequestPart;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\SupplierEntityInterface;
use App\Entity\UpdatableStatusEntityInterface;
use App\Entity\User;
use App\Filter\ColumnsFilter;
use App\Filter\SimpleSearchFilter;
use App\ION\Validator\Constraints\MasterData\BusinessPartners\BusinessPartnerSupplier;
use App\SageParts\P21\Validator\Constraints\SagePartsSupplier;
use App\Serializer\Normalizer\ActivityNormalizer;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Entity\LegacySupplierEntityInterface;
use LegacyBundle\Filter\SupplierFilter;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'csv', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => ['supplier_corrective_action_request', 'people_public', 'location_public', 'supplier']]
        ),
        new Delete(security: "is_granted('SUPPLIER_CORRECTIVE_ACTION_REQUEST_DELETE_VOTER', object)"),
        new Delete(
            uriTemplate: '/supplier_corrective_action_requests/{id}/files/{fileId}',
            uriVariables: [
                'id' => new Link(fromClass: SupplierCorrectiveActionRequest::class),
                'fileId' => new Link(toProperty: 'files', fromClass: SupplierCorrectiveActionRequestFile::class),
            ],
            defaults: ['parentProperty' => 'supplierCorrectiveActionRequest', 'class' => SupplierCorrectiveActionRequestFile::class],
            controller: DeleteController::class,
            normalizationContext: [],
            security: "is_granted('FEATURE_SCAR_FILE_DELETE')",
            name: 'delete_supplier_corrective_action_request_file',
        ),
        new Delete(
            uriTemplate: '/supplier_corrective_action_requests/{id}/main_file/{fileId}',
            uriVariables: [
                'id' => new Link(fromClass: SupplierCorrectiveActionRequest::class),
                'fileId' => new Link(toProperty: 'mainFiles', fromClass: SupplierCorrectiveActionRequestMainFile::class),
            ],
            defaults: ['parentProperty' => 'supplierCorrectiveActionRequest', 'class' => SupplierCorrectiveActionRequestMainFile::class],
            controller: DeleteController::class,
            name: 'delete_main_supplier_corrective_action_request_file',
        ),
        new Get(),
        new Get(
            uriTemplate: '/supplier_corrective_action_requests/{id}/files/{fileId}',
            uriVariables: [
                'id' => new Link(fromClass: SupplierCorrectiveActionRequest::class),
                'fileId' => new Link(toProperty: 'files', fromClass: SupplierCorrectiveActionRequestFile::class),
            ],
            defaults: ['parentProperty' => 'supplierCorrectiveActionRequest', 'class' => SupplierCorrectiveActionRequestFile::class],
            controller: DownloadController::class,
            normalizationContext: [],
            name: 'download_supplier_corrective_action_request_file'
        ),
        new Get(
            uriTemplate: '/supplier_corrective_action_requests/{id}/main_file/{fileId}',
            uriVariables: [
                'id' => new Link(fromClass: SupplierCorrectiveActionRequest::class),
                'fileId' => new Link(toProperty: 'mainFiles', fromClass: SupplierCorrectiveActionRequestMainFile::class),
            ],
            defaults: ['parentProperty' => 'supplierCorrectiveActionRequest', 'class' => SupplierCorrectiveActionRequestMainFile::class],
            controller: DownloadController::class,
            normalizationContext: [],
            name: 'download_main_supplier_corrective_action_request_file'
        ),
        new Put(security: "is_granted('ACCESS_PEOPLE')"),
        new Put(
            uriTemplate: '/supplier_corrective_action_requests/{id}/status',
            denormalizationContext: ['groups' => ['supplier_corrective_action_request:edit_status']],
            name: 'update_supplier_corrective_action_request_status',
        ),
        new Post(
            denormalizationContext: ['groups' => ['supplier_corrective_action_request:create', 'part:admin']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
            securityPostDenormalize: "is_granted('ACCESS_PEOPLE') or is_granted('BUSINESS_PARTNER_VOTER', object)",
        ),
        new Post(
            uriTemplate: '/supplier_corrective_action_requests/{id}/main_file',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getMainFiles', 'class' => SupplierCorrectiveActionRequestMainFile::class],
            controller: UploadController::class,
            normalizationContext: [],
            deserialize: false,
            name: 'upload_main_supplier_corrective_action_request_file',
        ),
        new Post(
            uriTemplate: '/supplier_corrective_action_requests/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => SupplierCorrectiveActionRequestFile::class],
            controller: UploadController::class,
            normalizationContext: [],
            deserialize: false,
            name: 'upload_supplier_corrective_action_request_file',
        ),
    ],
    routePrefix: 'quality',
    normalizationContext: ['groups' => SupplierCorrectiveActionRequest::ITEM_NORMALIZATION_GROUPS, ActivityNormalizer::NORMALIZE_ACTIVITY_ATTRIBUTE => 'comment'],
    denormalizationContext: ['groups' => ['supplier_corrective_action_request:edit', 'part:admin']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
    extraProperties: [
        ActivityNormalizer::DISCRIMINATOR_FILTER => [SupplierCorrectiveActionRequest::SCAR_COMMENT_DISCRIMINATOR],
        Comment::VENDOR_USER_COMMENTABLE => true,
    ]
)]
#[ORM\Table]
#[ApiFilter(SupplierFilter::class)]
#[ApiFilter(SearchFilter::class, properties: [
    'iFactor',
    'factory',
    'factory.name',
    'shortDescription' => 'partial',
    'poster',
    'leader',
    'representative',
    'supplierNumber',
    'supplierName' => 'partial',
    'parts.partNumber' => 'partial',
    'status',
    'id',
])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id' => 'exact',
    'shortDescription' => 'partial',
    'factory.name' => 'partial',
    'poster.lastname' => 'partial',
    'poster.firstname' => 'partial',
    'leader.lastname' => 'partial',
    'leader.firstname' => 'partial',
    'representative.lastname' => 'partial',
    'representative.firstname' => 'partial',
    'supplierNumber' => 'partial',
    'supplierName' => 'partial',
    'parts.partNumber' => 'partial',
    'status' => 'partial',
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt' => 'exact', 'closedAt' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: [
    'id',
    'createdAt',
    'closedAt',
    'shortDescription',
    'factory.name',
    'poster.lastname',
    'leader.lastname',
    'representative.lastname',
    'supplierNumber',
    'supplierName',
    'status',
    'iFactor',
])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => ['workflow', 'supplier_corrective_action_request:detail', 'part']])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalizationGroupsOverride', 'overrideDefaultGroups' => true, 'whitelist' => ['supplier_corrective_action_request:list']])]
#[ApiFilter(ColumnsFilter::class)]
#[App\Loggable]
#[Gedmo\SoftDeleteable]
class SupplierCorrectiveActionRequest implements SupplierEntityInterface, LegacySupplierEntityInterface, UpdatableStatusEntityInterface
{
    /** @var string */
    final public const CLOSED = 'CLOSED';

    /** @var string */
    final public const CANCEL = 'CANCEL';

    /** @var string */
    final public const PENDING = 'PENDING';

    /** @var string */
    final public const VENDOR_TO_FILL_FORM = 'VENDOR TO FILL FORM';

    /** @var string */
    final public const TLD_TO_REVIEW_FORM = 'TLD TO REVIEW FORM';

    /** @var string */
    final public const VALIDATION = 'VALIDATION';

    /** @var string */
    final public const COMMERCIAL_AGREEMENT = 'COMMERCIAL AGREEMENT';

    /** @var string */
    final public const IMPORTANCE_FACTOR_1 = 'IF1';

    /** @var string */
    final public const IMPORTANCE_FACTOR_10 = 'IF10';

    /** @var string */
    final public const IMPORTANCE_FACTOR_100 = 'IF100';

    /** @var string */
    final public const IMPORTANCE_FACTOR_1000 = 'IF1000';

    /** @var string */
    final public const SCAR_COMMENT_DISCRIMINATOR = 'scar_conversation';

    /** @var string[] */
    final public const ITEM_NORMALIZATION_GROUPS = ['supplier_corrective_action_request', 'supplier_corrective_action_request:detail', 'people_public', 'location_public', 'part', 'file', 'vendor_warranty_claim:light', 'vendor_warranty_claim_status', 'vendor_warranty_claim_type', 'non_conformity:light'];

    #[ORM\Column(type: 'datetime')]
    #[Groups(['supplier_corrective_action_request'])]
    #[Gedmo\Timestampable(on: 'create')]
    public \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['supplier_corrective_action_request'])]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: [self::CLOSED])]
    public ?\DateTime $closedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['supplier_corrective_action_request:detail'])]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: [self::VALIDATION])]
    public ?\DateTime $approvedAt = null;

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: [self::IMPORTANCE_FACTOR_1, self::IMPORTANCE_FACTOR_10, self::IMPORTANCE_FACTOR_100, self::IMPORTANCE_FACTOR_1000])]
    #[Groups(['supplier_corrective_action_request', 'supplier_corrective_action_request:create', 'supplier_corrective_action_request:edit', 'supplier_corrective_action_request:create_vendor'])]
    public string $iFactor;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['supplier_corrective_action_request', 'supplier_corrective_action_request:create', 'supplier_corrective_action_request:edit', 'supplier_corrective_action_request:create_vendor'])]
    #[ValidLocation(factory: true)]
    public Location $factory;

    #[ORM\Column(type: 'string')]
    #[Groups(['supplier_corrective_action_request', 'supplier_corrective_action_request:create', 'supplier_corrective_action_request:edit', 'supplier_corrective_action_request:list', 'supplier_corrective_action_request:create_vendor'])]
    public string $shortDescription;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['supplier_corrective_action_request:detail', 'supplier_corrective_action_request:create', 'supplier_corrective_action_request:edit', 'supplier_corrective_action_request:create_vendor'])]
    public string $description;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['supplier_corrective_action_request:detail', 'supplier_corrective_action_request:edit', 'supplier_corrective_action_request:create_vendor'])]
    public ?string $issueOrigin = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['supplier_corrective_action_request:detail', 'supplier_corrective_action_request:edit', 'supplier_corrective_action_request:create_vendor'])]
    public ?string $correctiveAction = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['supplier_corrective_action_request:detail', 'supplier_corrective_action_request:edit'])]
    public ?string $commercialAgreement = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['supplier_corrective_action_request', 'supplier_corrective_action_request:create', 'supplier_corrective_action_request:edit'])]
    public ?People $representative = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['supplier_corrective_action_request'])]
    #[Gedmo\Blameable(on: 'create')]
    public ?User $poster;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Purchasing\VendorUser')]
    #[Groups(['supplier_corrective_action_request:detail', 'supplier_corrective_action_request:create', 'supplier_corrective_action_request:edit'])]
    public ?VendorUser $supplierRepresentative = null;
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['supplier_corrective_action_request', 'supplier_corrective_action_request:create', 'supplier_corrective_action_request:edit_full'])]
    public ?People $leader = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['supplier_corrective_action_request:detail', 'supplier_corrective_action_request:edit'])]
    public ?string $verificationDescription = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['supplier_corrective_action_request:detail', 'supplier_corrective_action_request:edit'])]
    public ?string $preventiveAction = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['supplier_corrective_action_request:detail', 'supplier_corrective_action_request:edit'])]
    public ?string $conclusion = null;

    #[ORM\Column(name: 'deleted_at', type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $deletedAt = null;

    #[Groups(['supplier_corrective_action_request:create_vendor'])]
    public ?string $posterInformation = null;

    #[Groups(['supplier_corrective_action_request:create'])]
    public ?NonConformity $nonConformity = null;

    /**
     * @var Collection<VendorWarrantyClaim>
     */
    #[ORM\OneToMany(mappedBy: 'supplierCorrectiveActionRequest', targetEntity: 'App\Entity\Purchasing\VendorWarrantyClaim')]
    #[MaxDepth(1)]
    #[Groups(['supplier_corrective_action_request:detail', 'supplier_corrective_action_request:create_vendor'])]
    private Collection $vendorWarrantyClaims;

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: [self::PENDING, self::VENDOR_TO_FILL_FORM, self::TLD_TO_REVIEW_FORM, self::VALIDATION, self::COMMERCIAL_AGREEMENT, self::CLOSED])]
    #[Groups(['supplier_corrective_action_request', 'supplier_corrective_action_request:edit_status'])]
    private string $status = self::PENDING;

    #[ORM\Column(type: 'string')]
    #[Groups(['supplier_corrective_action_request'])]
    private string $supplierName;

    #[Assert\When(
        expression: 'this.getLocation().getErpSoftware() === "LN"',
        constraints: new BusinessPartnerSupplier()
    )]
    #[Assert\When(
        expression: 'this.getLocation().getErpSoftware() === "P21"',
        constraints: new SagePartsSupplier()
    )]
    #[ORM\Column(type: 'string')]
    #[Groups(['supplier_corrective_action_request', 'supplier_corrective_action_request:create', 'supplier_corrective_action_request:edit', 'supplier_corrective_action_request:create_vendor'])]
    private string $supplierNumber;

    /**
     * @todo remove groups with ln-compatibility
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['supplier_corrective_action_request'])]
    private ?int $supplierErp = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $baanSupplierNumber = null;

    /**
     * @var Collection<SupplierCorrectiveActionRequestPart>
     */
    #[ORM\OneToMany(mappedBy: 'supplierCorrectiveActionRequest', targetEntity: 'App\Entity\Parts\SupplierCorrectiveActionRequestPart', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['supplier_corrective_action_request:detail', 'supplier_corrective_action_request:create', 'supplier_corrective_action_request:edit', 'supplier_corrective_action_request:create_vendor'])]
    private Collection $parts;

    /**
     * @var Collection<SupplierCorrectiveActionRequestFile>
     */
    #[ORM\OneToMany(mappedBy: 'supplierCorrectiveActionRequest', targetEntity: 'App\Entity\Quality\SupplierCorrectiveActionRequestFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['supplier_corrective_action_request:detail'])]
    private Collection $files;

    /**
     * @var Collection<Location>
     */
    #[Groups(['supplier_corrective_action_request:create'])]
    private Collection $affectedFactories;

    /**
     * @var Collection<SupplierCorrectiveActionRequestMainFile>
     */
    #[ORM\OneToMany(mappedBy: 'supplierCorrectiveActionRequest', targetEntity: 'App\Entity\Quality\SupplierCorrectiveActionRequestMainFile', cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(max: 1)]
    private Collection $mainFiles;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['supplier_corrective_action_request', 'supplier_corrective_action_request:list'])]
    private int $id;

    public function __construct()
    {
        $this->parts = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->mainFiles = new ArrayCollection();
        $this->affectedFactories = new ArrayCollection();
        $this->vendorWarrantyClaims = new ArrayCollection();
    }

    public function getSupplierName(): string
    {
        return $this->supplierName;
    }

    public function setSupplierName(string $name): self
    {
        $this->supplierName = $name;

        return $this;
    }

    public function getSupplierNumber(): string
    {
        return $this->supplierNumber;
    }

    public function setSupplierNumber(string $supplierNumber): self
    {
        $this->supplierNumber = $supplierNumber;

        return $this;
    }

    public function getSupplierErp(): ?int
    {
        return $this->supplierErp;
    }

    /**
     * @return Collection<SupplierCorrectiveActionRequestPart>
     */
    public function getParts()
    {
        return $this->parts;
    }

    public function addPart(SupplierCorrectiveActionRequestPart $part): self
    {
        if (!$this->parts->contains($part)) {
            $this->parts->add($part);
            $part->supplierCorrectiveActionRequest = $this;
        }

        return $this;
    }

    public function removePart(SupplierCorrectiveActionRequestPart $part): self
    {
        if ($this->parts->contains($part)) {
            $this->parts->removeElement($part);
        }

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<SupplierCorrectiveActionRequestFile>
     */
    public function getFiles()
    {
        return $this->files;
    }

    public function addFile(SupplierCorrectiveActionRequestFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setSupplierCorrectiveActionRequest($this);
        }

        return $this;
    }

    public function removeFile(SupplierCorrectiveActionRequestFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }

        return $this;
    }

    /**
     * @return Collection<Location>
     */
    public function getAffectedFactories()
    {
        return $this->affectedFactories;
    }

    public function addAffectedFactory(Location $affectedFactory): self
    {
        if (!$this->affectedFactories->contains($affectedFactory)) {
            $this->affectedFactories->add($affectedFactory);
        }

        return $this;
    }

    public function removeAffectedFactory(Location $affectedFactory): self
    {
        if (!$this->affectedFactories->contains($affectedFactory)) {
            $this->affectedFactories->removeElement($affectedFactory);
        }

        return $this;
    }

    #[Groups(['supplier_corrective_action_request:detail'])]
    public function getMainFile(): ?SupplierCorrectiveActionRequestMainFile
    {
        if (0 === $this->mainFiles->count()) {
            return null;
        }

        return $this->mainFiles->first();
    }

    public function getMainFiles(): Collection
    {
        return $this->mainFiles;
    }

    public function setMainFile(?SupplierCorrectiveActionRequestMainFile $mainFile): self
    {
        if (null === $mainFile) {
            $this->mainFiles = new ArrayCollection();

            return $this;
        }

        return $this->addMainFile($mainFile);
    }

    public function addMainFile(SupplierCorrectiveActionRequestMainFile $mainFile): self
    {
        if (!$this->mainFiles->contains($mainFile)) {
            $mainFile->setSupplierCorrectiveActionRequest($this);
            $this->mainFiles->add($mainFile);
        }

        return $this;
    }

    public function removeMainFile(SupplierCorrectiveActionRequestMainFile $mainFile): self
    {
        if ($this->mainFiles->contains($mainFile)) {
            $this->mainFiles->removeElement($mainFile);
        }

        return $this;
    }

    public function getBusinessPartnerCode(): string
    {
        return $this->getSupplierNumber();
    }

    /**
     * @return Collection<VendorWarrantyClaim>
     */
    public function getVendorWarrantyClaims()
    {
        return $this->vendorWarrantyClaims;
    }

    public function addVendorWarrantyClaim(VendorWarrantyClaim $vendorWarrantyClaim): self
    {
        if (!$this->vendorWarrantyClaims->contains($vendorWarrantyClaim)) {
            $this->vendorWarrantyClaims->add($vendorWarrantyClaim);
            $vendorWarrantyClaim->supplierCorrectiveActionRequest = $this;
        }

        return $this;
    }

    public function removeVendorWarrantyClaim(VendorWarrantyClaim $vendorWarrantyClaim): self
    {
        if ($this->vendorWarrantyClaims->contains($vendorWarrantyClaim)) {
            $this->vendorWarrantyClaims->removeElement($vendorWarrantyClaim);
        }

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->factory;
    }
}
