<?php

declare(strict_types=1);

namespace App\Entity\Purchasing;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\DataProvider\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimStatisticsDataProvider;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Dto\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimStatisticsDto;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Finance\Currency;
use App\Entity\Parts\VendorWarrantyClaimPart;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\Entity\SupplierEntityInterface;
use App\Filter\ColumnsFilter;
use App\Filter\Purchasing\NCRVendorWarrantyClaimIdFilter;
use App\Filter\Purchasing\VendorWarrantyClaimOpenFilter;
use App\Filter\Purchasing\WCVendorWarrantyClaimIdFilter;
use App\ION\Validator\Constraints\MasterData\BusinessPartners\BusinessPartnerSupplier;
use App\Serializer\Normalizer\ActivityNormalizer;
use App\Validator\Constraints\LocationOr as ValidOrLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Entity\LegacySupplierEntityInterface;
use LegacyBundle\Filter\SupplierFilter;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: 'App\Repository\Purchasing\VendorWarrantyClaimRepository')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['ncr' => 'App\Entity\Purchasing\NCRVendorWarrantyClaim', 'wc' => 'App\Entity\Purchasing\WCVendorWarrantyClaim'])]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            paginationItemsPerPage: 2000,
            normalizationContext: ['groups' => VendorWarrantyClaim::COLLECTION_NORMALIZATION_GROUPS],
        ),
        new Get(),
        new Get(
            uriTemplate: '/vendor_warranty_claims/{id}/files/{fileId}',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: VendorWarrantyClaimFile::class),
                'id' => new Link(fromClass: VendorWarrantyClaim::class),
            ],
            defaults: ['parentProperty' => 'vendorWarrantyClaim', 'class' => VendorWarrantyClaimFile::class],
            controller: DownloadController::class,
            normalizationContext: [],
            name: 'download_vendor_warranty_claim_file',
        ),
        new Post(
            uriTemplate: '/vendor_warranty_claims/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => VendorWarrantyClaimFile::class],
            controller: UploadController::class,
            normalizationContext: [],
            deserialize: false,
            name: 'upload_vendor_warranty_claim_file'
        ),
        new Delete(security: "is_granted('FEATURE_VENDOR_WARRANTY_CLAIM_DELETE')"),
        new Delete(
            uriTemplate: '/vendor_warranty_claims/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: VendorWarrantyClaimFile::class),
                'id' => new Link(fromClass: VendorWarrantyClaim::class),
            ],
            defaults: ['parentProperty' => 'vendorWarrantyClaim', 'class' => VendorWarrantyClaimFile::class],
            controller: DeleteController::class,
            normalizationContext: [],
            security: "is_granted('FEATURE_VENDOR_WARRANTY_CLAIM_FILE_DELETE')",
            name: 'delete_vendor_warranty_claim_file'
        ),
        new Get(
            uriTemplate: '/vendor_warranty_claims_statistics',
            normalizationContext: ['groups' => ['vendor_warranty_claim_statistics']],
            output: VendorWarrantyClaimStatisticsDto::class,
            name: 'vendor_warranty_claim_statistics',
            provider: VendorWarrantyClaimStatisticsDataProvider::class,
        ),
    ],
    routePrefix: 'purchasing',
    normalizationContext: ['groups' => VendorWarrantyClaim::ITEM_NORMALIZATION_GROUPS, ActivityNormalizer::NORMALIZE_ACTIVITY_ATTRIBUTE => 'both'],
    denormalizationContext: [],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
)]
#[ORM\Table(name: 'vendor_warranty_claims')]
#[ORM\Index(columns: ['closed_at'])]
#[ORM\Index(columns: ['supplier_number'])]
#[ApiFilter(VendorWarrantyClaimOpenFilter::class)]
#[ApiFilter(WCVendorWarrantyClaimIdFilter::class)]
#[ApiFilter(NCRVendorWarrantyClaimIdFilter::class)]
#[ApiFilter(SupplierFilter::class)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'requestedCreditAmount', 'status.name', 'createdAt', 'closedAt', 'statusUpdatedAt', 'supplierName', 'supplierNumber', 'assignee.lastname', 'requestedSupplierAction', 'supplierCorrectiveActionRequest.id', 'supplierCreditAmount', 'actualCreditAmount', 'currency.name'])]
#[ApiFilter(DateFilter::class, properties: ['closedAt', 'createdAt', 'vendorToRespondAt'])]
#[ApiFilter(SearchFilter::class, properties: ['status', 'status.name', 'location', 'factory', 'parts.partNumber' => 'partial', 'supplierErp', 'supplierNumber', 'supplierName' => 'partial', 'assignee', 'assignee.department', 'accepted', 'parts.serialNumber', 'poster'])]
#[ApiFilter(RangeFilter::class, properties: ['supplierCreditAmount'])]
#[ApiFilter(ExistsFilter::class, properties: ['assignee'])]
#[ApiFilter(ColumnsFilter::class)]
#[App\Loggable]
abstract class VendorWarrantyClaim implements SupplierEntityInterface, LegacySupplierEntityInterface
{
    /** @var string[] */
    public const ITEM_NORMALIZATION_GROUPS = ['vendor_warranty_claim', 'vendor_warranty_claim:detail', 'vendor_warranty_claim_type', 'non_conformity', 'non_conformity:main_file', 'non_conformity:files', 'supplier_corrective_action_request', 'people_public', 'location_public', 'supplier', 'part', 'currency', 'file', 'vendor_warranty_claim_status', 'equipment_list', 'process'];

    /** @var string[] */
    public const COLLECTION_NORMALIZATION_GROUPS = ['vendor_warranty_claim', 'vendor_warranty_claim_type', 'non_conformity', 'supplier_corrective_action_request', 'people_public', 'location_public', 'supplier', 'part', 'vendor_warranty_claim_status', 'process', 'currency'];

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\SupplierCorrectiveActionRequest', inversedBy: 'vendorWarrantyClaims')]
    #[MaxDepth(1)]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:create', 'vendor_warranty_claim:edit'])]
    public ?SupplierCorrectiveActionRequest $supplierCorrectiveActionRequest = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Purchasing\VendorWarrantyClaimType')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\NotNull]
    #[Groups(['vendor_warranty_claim:detail', 'vendor_warranty_claim:create', 'vendor_warranty_claim:edit', 'vendor_warranty_claim:light'])]
    public ?VendorWarrantyClaimType $type = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:create', 'vendor_warranty_claim:edit'])]
    public bool $scarRequested = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['vendor_warranty_claim'])]
    #[Gedmo\Timestampable(on: 'change', field: 'status')]
    public ?\DateTimeInterface $statusUpdatedAt = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: true)]
    public ?Location $factory = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['vendor_warranty_claim:detail', 'vendor_warranty_claim:create', 'vendor_warranty_claim:edit', 'vendor_warranty_claim'])]
    #[ValidOrLocation(sso: true, factory: true, sparePartsHub: true)]
    public Location $location;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['vendor_warranty_claim:detail'])]
    #[Gedmo\Blameable(on: 'create')]
    public People $poster;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:edit'])]
    #[Transferable]
    public ?People $assignee;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Purchasing\VendorWarrantyClaimStatus')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:light'])]
    public VendorWarrantyClaimStatus $status;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['vendor_warranty_claim'])]
    #[Gedmo\Timestampable(on: 'create')]
    public \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['vendor_warranty_claim'])]
    public ?\DateTimeInterface $closedAt = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:create', 'vendor_warranty_claim:edit'])]
    public string $requestedSupplierAction;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:create', 'vendor_warranty_claim:edit'])]
    public ?float $requestedCreditAmount = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['vendor_warranty_claim:detail', 'vendor_warranty_claim:edit_shipping', 'vendor_warranty_claim:evendor_edit'])]
    public ?string $supplierReturnMerchandiseAuthorization = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['vendor_warranty_claim:detail', 'vendor_warranty_claim:edit_finance'])]
    public ?string $supplierCreditNote = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:edit_finance', 'vendor_warranty_claim:evendor_edit'])]
    public ?float $supplierCreditAmount = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:edit'])]
    public ?float $actualCreditAmount = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['vendor_warranty_claim:detail', 'vendor_warranty_claim:edit_shipping', 'vendor_warranty_claim:evendor_edit'])]
    public ?string $supplierShippingInstruction = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:create', 'vendor_warranty_claim:edit'])]
    public ?string $supplierStockVerified = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:create', 'vendor_warranty_claim:edit'])]
    public ?string $tldStockVerified = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:create', 'vendor_warranty_claim:edit'])]
    public ?string $issueOrigin = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:create', 'vendor_warranty_claim:edit'])]
    public ?string $correctiveAction = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['vendor_warranty_claim:detail', 'vendor_warranty_claim:edit'])]
    public ?string $supplierShipperName = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['vendor_warranty_claim:detail', 'vendor_warranty_claim:evendor_edit'])]
    public bool $accepted = false;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['vendor_warranty_claim:detail', 'vendor_warranty_claim:edit'])]
    public ?string $costBreakdown = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['vendor_warranty_claim:detail', 'vendor_warranty_claim:evendor_edit'])]
    public bool $shipBackDefectivePart = false;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['vendor_warranty_claim:detail'])]
    public ?string $resolution = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['vendor_warranty_claim:detail', 'vendor_warranty_claim:edit_shipping'])]
    public ?string $trackingNumber = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Finance\Currency')]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:create', 'vendor_warranty_claim:edit'])]
    public ?Currency $currency = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['vendor_warranty_claim'])]
    #[Gedmo\Timestampable(on: 'update', field: 'status.name', value: 'VENDOR_TO_RESPOND')]
    public ?\DateTimeInterface $vendorToRespondAt = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['vendor_warranty_claim'])]
    private ?string $supplierName = null;

    #[BusinessPartnerSupplier]
    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:create', 'vendor_warranty_claim:edit'])]
    private ?string $supplierNumber = null;

    /**
     * @todo remove groups with ln-compatibility
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['vendor_warranty_claim'])]
    private ?int $supplierErp = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $baanSupplierNumber = null;

    /**
     * @var Collection<VendorWarrantyClaimPart>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Parts\VendorWarrantyClaimPart', mappedBy: 'vendorWarrantyClaim', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:create', 'vendor_warranty_claim:edit'])]
    private Collection $parts;

    /**
     * @var Collection<VendorWarrantyClaimFile>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Purchasing\VendorWarrantyClaimFile', mappedBy: 'vendorWarrantyClaim', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['vendor_warranty_claim:detail'])]
    private Collection $files;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:light'])]
    private int $id;

    public function __construct()
    {
        $this->parts = new ArrayCollection();
        $this->files = new ArrayCollection();
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

    /**
     * @return Collection<VendorWarrantyClaimPart>
     */
    public function getParts()
    {
        return $this->parts;
    }

    public function addPart(VendorWarrantyClaimPart $part): self
    {
        if (!$this->parts->contains($part)) {
            $this->parts->add($part);
            $part->vendorWarrantyClaim = $this;
        }

        return $this;
    }

    public function removePart(VendorWarrantyClaimPart $part): self
    {
        if ($this->parts->contains($part)) {
            $this->parts->removeElement($part);
        }

        return $this;
    }

    /**
     * @return Collection<VendorWarrantyClaimFile>
     */
    public function getFiles()
    {
        return $this->files;
    }

    public function addFile(VendorWarrantyClaimFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setVendorWarrantyClaim($this);
        }

        return $this;
    }

    public function removeFile(VendorWarrantyClaimFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBusinessPartnerCode(): ?string
    {
        return $this->getSupplierNumber();
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if (\in_array($this->status->name, VendorWarrantyClaimStatus::CLOSED_STATUSES, true) && $this->scarRequested && null === $this->supplierCorrectiveActionRequest) {
            $context->buildViolation('Cannot close a VWC that requires a SCAR.')
                ->atPath('status')
                ->addViolation();
        }

        if (VendorWarrantyClaimStatus::VALIDATE_SCAR === $this->status->name && !$this->scarRequested) {
            $context->buildViolation('Status not allowed, VWC does not required a SCAR.')
                ->atPath('status')
                ->addViolation();
        }
    }
}
