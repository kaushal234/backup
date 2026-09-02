<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
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
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\DataProcessor\Parts\SparePartsRequestCombineInputDataProcessor;
use App\Dto\Parts\SparePartsRequestCombineInput;
use App\Entity\Common\Airport;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\UpdatableStatusEntityInterface;
use App\Filter\ColumnsFilter;
use App\Filter\DiscriminatorFilter;
use App\Filter\Parts\SparePartsRequestTimeToDispatch;
use App\ION\Manager\MasterData\EnterpriseModel\EnterpriseStructure\SiteToLocationConverter;
use App\ION\Resources\Sales\SalesOrder as IONSalesOrder;
use App\ION\Resources\Warehousing\Shipments\Shipment;
use App\ION\Validator\Constraints\Sales\SalesOrder;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['toc' => 'App\Entity\Parts\TOCSparePartsRequest', 'sb' => 'App\Entity\Parts\SBSparePartsRequest'])]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'csv', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => SparePartsRequest::COLLECTION_NORMALIZATION_GROUPS]),
        new Put(
            uriTemplate: '/spare_parts_requests/{id}/status',
            denormalizationContext: ['groups' => ['spare_parts_request:update_status']],
            security: "is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL') or is_granted('MOO_SPR') or is_granted('FEATURE_SPARE_PARTS_REQUESTS_SHIPPED_TO_CLOSE')",
            name: 'update_spare_parts_request_status',
        ),
        new Put(
            uriTemplate: '/spare_parts_requests/{id}/combine',
            // The body describes the SPR to merge in, not the SPR being updated: assigning it as
            // object to populate would narrow the expected class of every nested IRI to its own.
            denormalizationContext: [SerializerContextBuilderInterface::ASSIGN_OBJECT_TO_POPULATE => false],
            security: "is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_PARTS') or is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL') or is_granted('MOO_SPR')",
            input: SparePartsRequestCombineInput::class,
            validate: false,
            name: 'combine',
            processor: SparePartsRequestCombineInputDataProcessor::class
        ),
        new Get(),
        new Get(
            uriTemplate: '/spare_parts_requests/{id}/proof_of_delivery/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: ProofOfDeliveryFile::class),
                'id' => new Link(fromClass: SparePartsRequest::class),
            ],
            defaults: ['parentProperty' => 'sparePartsRequest', 'class' => ProofOfDeliveryFile::class],
            controller: DownloadController::class,
            name: 'download_proof_of_delivery',
        ),
        new Post(
            uriTemplate: '/spare_parts_requests/{id}/proof_of_delivery',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getProofOfDelivery', 'class' => ProofOfDeliveryFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL') or is_granted('MOO_SPR') or is_granted('FEATURE_SPARE_PARTS_REQUESTS_SHIPPED_TO_CLOSE')",
            deserialize: false,
            name: 'upload_proof_of_delivery',
        ),
        new Delete(
            uriTemplate: '/spare_parts_requests/{id}/proof_of_delivery/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: ProofOfDeliveryFile::class),
                'id' => new Link(fromClass: SparePartsRequest::class),
            ],
            defaults: ['parentProperty' => 'sparePartsRequest', 'class' => ProofOfDeliveryFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL') or is_granted('MOO_SPR')",
            name: 'delete_proof_of_delivery',
        ),
        new Get(
            uriTemplate: '/spare_parts_requests/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'sparePartsRequestFiles', fromClass: SparePartsRequestFile::class),
                'id' => new Link(fromClass: SparePartsRequest::class),
            ],
            defaults: ['parentProperty' => 'sparePartsRequest', 'class' => SparePartsRequestFile::class],
            controller: DownloadController::class,
            name: 'download_spare_parts_request_file',
        ),
        new Post(
            uriTemplate: '/spare_parts_requests/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getSparePartsRequestFiles', 'class' => SparePartsRequestFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_SPARE_PARTS_REQUESTS_FILE_UPLOAD') or is_granted('MOO_SPR')",
            deserialize: false,
            name: 'upload_spare_parts_request_file',
        ),
        new Delete(
            uriTemplate: '/spare_parts_requests/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'sparePartsRequestFiles', fromClass: SparePartsRequestFile::class),
                'id' => new Link(fromClass: SparePartsRequest::class),
            ],
            defaults: ['parentProperty' => 'sparePartsRequest', 'class' => SparePartsRequestFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_SPARE_PARTS_REQUESTS_FILE_DELETE') or is_granted('MOO_SPR')",
            name: 'delete_spare_parts_request_file',
        ),
    ],
    routePrefix: 'parts',
    normalizationContext: ['groups' => SparePartsRequest::ITEM_NORMALIZATION_GROUPS],
    denormalizationContext: [],
)]
#[ORM\Table(name: 'spare_parts_requests')]
#[ApiFilter(ColumnsFilter::class)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'createdAt', 'status', 'sph.name', 'factory.name', 'airport.code', 'salesOrder', 'customer.name', 'activity', 'type'])]
#[ApiFilter(SearchFilter::class, properties: ['legacyId', 'poster', 'sph', 'factory', 'parts.shippingOrigin', 'airport', 'status', 'type', 'parts.partNumber', 'salesOrder' => 'partial', 'sso.legacyId', 'customer', 'deliveryAddress'])]
#[ApiFilter(DateFilter::class, properties: ['shippingDate', 'createdAt'])]
#[ApiFilter(ExistsFilter::class, properties: ['salesOrder'])]
#[ApiFilter(SparePartsRequestTimeToDispatch::class)]
#[ApiFilter(DiscriminatorFilter::class)]
#[Legacy\Synchronize(table: 'spr')]
#[Legacy\ExtraColumn(column: 'parent_id', value: 0)]
#[Legacy\ExtraColumn(column: 'psr_id', value: 0)]
abstract class SparePartsRequest implements UpdatableStatusEntityInterface
{
    use LegacyIdentifierTrait;

    /** @var string */
    public const STATUS_PENDING = 'PENDING';

    /** @var string */
    public const STATUS_OPEN = 'OPEN';

    /** @var string */
    public const STATUS_SHIPPED = 'SHIPPED';

    /** @var string */
    public const STATUS_CLOSED = 'CLOSED';

    /** @var string */
    public const STATUS_MERGED = 'MERGED';

    /** @var array */
    public const STATUS_ALL = [self::STATUS_PENDING, self::STATUS_OPEN, self::STATUS_SHIPPED, self::STATUS_CLOSED];

    /** @var string */
    public const ACTIVITY_TROUBLESHOOTING = 'Troubleshooting';

    /** @var string */
    public const ACTIVITY_COMMISSIONING = 'Commissioning';

    /** @var string */
    public const ACTIVITY_SERVICE_BULLETIN = 'Service Bulletin';

    /** @var string */
    public const ACTIVITY_TRAINING = 'Training';

    /** @var string */
    public const ACTIVITY_MAINTENANCE = 'Maintenance';

    /** @var string */
    public const ACTIVITY_UNIT_UPGRADE = 'Unit Upgrade';

    /** @var string */
    public const TYPE_UNDEFINED = 'Not Defined Yet';

    /** @var string */
    public const TYPE_WARRANTY = 'Warranty';

    /** @var string */
    public const TYPE_SSO = 'SSO';

    /** @var string */
    public const TYPE_PAYABLE_SERVICES = 'Payable Services';

    /** @var string[] */
    public const ITEM_NORMALIZATION_GROUPS = ['legacy_id', 'spare_parts_request', 'spare_parts_request:detail', 'part', 'location_public', 'people_public', 'airport_list', 'expose_legacy', 'extranet_user_list', 'equipment_record', 'customer_list', 'file', 'address'];

    /** @var string[] */
    public const COLLECTION_NORMALIZATION_GROUPS = ['legacy_id', 'spare_parts_request', 'location_public', 'people_public', 'airport_list', 'expose_legacy', 'customer_list'];

    #[ORM\Column(type: 'datetime')]
    #[Groups(['spare_parts_request'])]
    #[Legacy\Column(column: 'dt', transformer: DateTimeToString::class)]
    #[Gedmo\Timestampable(on: 'create')]
    public \DateTime $createdAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['spare_parts_request:detail'])]
    #[Legacy\Column(column: 'entered_by', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Legacy\Column(column: 'assignor', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Gedmo\Blameable(on: 'create')]
    public People $poster;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::ACTIVITY_TROUBLESHOOTING, self::ACTIVITY_COMMISSIONING, self::ACTIVITY_SERVICE_BULLETIN, self::ACTIVITY_TRAINING, self::ACTIVITY_MAINTENANCE, self::ACTIVITY_UNIT_UPGRADE])]
    #[Groups(['spare_parts_request', 'spare_parts_request:create', 'spare_parts_request:edit:partial'])]
    public string $activity;

    #[ORM\Column(type: 'string')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['spare_parts_request', 'spare_parts_request:create', 'spare_parts_request:edit:partial'])]
    public string $type = self::TYPE_UNDEFINED;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['spare_parts_request', 'spare_parts_request:create', 'spare_parts_request:edit:full'])]
    #[ValidLocation(sparePartsHub: true)]
    #[Legacy\Column(column: 'sph_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public Location $sph;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['spare_parts_request', 'spare_parts_request:create', 'spare_parts_request:edit:full'])]
    #[Legacy\Column(column: 'sso_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public Location $sso;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['spare_parts_request', 'spare_parts_request:create', 'spare_parts_request:edit:full'])]
    #[ValidLocation(factory: true)]
    public Location $factory;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    public ?Location $shippingOrigin = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[Assert\NotNull]
    #[Groups(['spare_parts_request', 'spare_parts_request:detail', 'spare_parts_request:create', 'spare_parts_request:edit:full'])]
    public Location $erpLocation;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['spare_parts_request'])]
    #[Legacy\Column(column: 'dt_ship', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: self::STATUS_SHIPPED)]
    public ?\DateTime $shippingDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['spare_parts_request', 'spare_parts_request:edit:full'])]
    #[Legacy\Column(column: 'estimated_shipping_date', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    public ?\DateTime $estimatedShippingDate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['spare_parts_request:detail', 'spare_parts_request:edit:full'])]
    public ?string $notes = null;

    #[ORM\Column(type: 'string', length: 9, nullable: true)]
    #[Groups(['spare_parts_request', 'spare_parts_request:edit:full'])]
    #[SalesOrder]
    public ?string $salesOrder = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['spare_parts_request', 'spare_parts_request:edit:full', 'spare_parts_request:create'])]
    #[Legacy\Column(column: 'cust_nama', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    public Customer $customer;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Common\Airport')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['spare_parts_request', 'spare_parts_request:edit:full', 'spare_parts_request:create'])]
    public ?Airport $airport = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['spare_parts_request:detail', 'spare_parts_request:edit:full'])]
    public ?string $deliveryNotes = null;

    #[ORM\Column(type: 'string')]
    #[Groups(['spare_parts_request', 'spare_parts_request:update_status'])]
    #[Legacy\Column(column: 'status')]
    protected string $status = self::STATUS_PENDING;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Parts\SparePartsRequestDeliveryAddress', cascade: ['persist'], inversedBy: 'sparePartsRequests')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Assert\Valid]
    #[Groups(['spare_parts_request:detail', 'spare_parts_request:edit:full', 'spare_parts_request:create'])]
    private SparePartsRequestDeliveryAddress $deliveryAddress;

    /**
     * @var Collection<EquipmentRecord>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\EquipmentRecord')]
    #[Assert\Count(min: 1)]
    #[Groups(['spare_parts_request:detail', 'spare_parts_request:create'])]
    private Collection $equipmentRecords;

    /**
     * @var Collection<SparePartsRequestPart>
     */
    #[ORM\OneToMany(mappedBy: 'sparePartsRequest', targetEntity: 'App\Entity\Parts\SparePartsRequestPart', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['spare_parts_request:detail', 'part', 'spare_parts_request:create', 'spare_parts_request:edit:partial'])]
    private Collection $parts;

    /**
     * @var Collection<ProofOfDeliveryFile>
     */
    #[ORM\OneToMany(mappedBy: 'sparePartsRequest', targetEntity: 'App\Entity\Parts\ProofOfDeliveryFile', cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(max: 1)]
    private Collection $files;

    /**
     * @var Collection<SparePartsRequestFile>
     */
    #[ORM\OneToMany(mappedBy: 'sparePartsRequest', targetEntity: 'App\Entity\Parts\SparePartsRequestFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['spare_parts_request:detail'])]
    private Collection $sparePartsRequestFiles;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['spare_parts_request', 'spare_parts_request:detail'])]
    private int $id;

    public function __construct()
    {
        $this->equipmentRecords = new ArrayCollection();
        $this->parts = new ArrayCollection();
        $this->sparePartsRequestFiles = new ArrayCollection();
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
        $this->equipmentRecords->add($equipmentRecord);

        return $this;
    }

    public function removeEquipmentRecord(EquipmentRecord $equipmentRecord): self
    {
        $this->equipmentRecords->removeElement($equipmentRecord);

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

    public function getDeliveryAddress(): SparePartsRequestDeliveryAddress
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(SparePartsRequestDeliveryAddress $deliveryAddress): self
    {
        $deliveryAddress->lastUsedAt = new \DateTime();
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    /**
     * @return Collection<SparePartsRequestPart>
     */
    public function getParts(): Collection
    {
        return new ArrayCollection($this->parts->filter(static fn (SparePartsRequestPart $part) => null === $part->deletedAt)->getValues());
    }

    /**
     * @return Collection<SparePartsRequestPart>
     */
    #[Groups(['part'])]
    public function getDeletedParts(): Collection
    {
        return new ArrayCollection($this->parts->filter(static fn (SparePartsRequestPart $part) => null !== $part->deletedAt)->getValues());
    }

    /**
     * @return Collection<SparePartsRequestPart>
     */
    public function getAllParts(): Collection
    {
        return $this->parts;
    }

    public function addPart(SparePartsRequestPart $part): self
    {
        $part->sparePartsRequest = $this;
        if (!$this->parts->contains($part)) {
            $this->parts->add($part);
        }

        return $this;
    }

    public function removePart(SparePartsRequestPart $part): self
    {
        $part->deletedAt = new \DateTime();

        return $this;
    }

    public function fullyRemovePart(SparePartsRequestPart $part): self
    {
        $this->parts->removeElement($part);

        return $this;
    }

    #[Groups(['spare_parts_request:detail'])]
    public function getProofOfDelivery(): ?ProofOfDeliveryFile
    {
        if (0 === $this->files->count()) {
            return null;
        }

        return $this->files->first();
    }

    public function setProofOfDelivery(?ProofOfDeliveryFile $file): self
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

    public function addFile(ProofOfDeliveryFile $file): self
    {
        $file->sparePartsRequest = $this;
        $this->files->add($file);

        return $this;
    }

    public function removeFile(ProofOfDeliveryFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }

    public function getSparePartsRequestFiles(): Collection
    {
        return $this->sparePartsRequestFiles;
    }

    public function addSparePartsRequestFile(SparePartsRequestFile $sparePartsRequestFile): self
    {
        $sparePartsRequestFile->sparePartsRequest = $this;
        $this->sparePartsRequestFiles->add($sparePartsRequestFile);

        return $this;
    }

    public function removeSparePartsRequestFile(SparePartsRequestFile $sparePartsRequestFile): self
    {
        if ($this->sparePartsRequestFiles->contains($sparePartsRequestFile)) {
            $this->sparePartsRequestFiles->removeElement($sparePartsRequestFile);
        }

        return $this;
    }

    public function importPartsFromSparePartsRequest(self $sparePartsRequest): void
    {
        foreach ($sparePartsRequest->getAllParts() as $part) {
            $this->addPartOrIncrementQuantity($part);
        }
    }

    public function processShipment(Shipment $shipment): void
    {
        if (null === $shipment->load->trackingNumber) {
            return;
        }

        foreach ($shipment->getLines() as $line) {
            if ($this->salesOrder !== $line->orderReference->order || !$line->orderReference->isSalesOrder()) {
                continue;
            }

            foreach ($this->getParts()->filter(static fn (SparePartsRequestPart $part) => $part->partNumber === $line->item) as $part) {
                $part->addTracking(PartTracking::fromShipmentLine($line));
            }
        }
    }

    public function processSalesOrder(IONSalesOrder $salesOrder, SiteToLocationConverter $siteToLocationConverter)
    {
        foreach ($salesOrder->getLines() as $salesOrderLine) {
            $matchingParts = $this->getParts()->filter(static fn (SparePartsRequestPart $part) => $part->partNumber === $salesOrderLine->item);
            foreach ($matchingParts as $matchingPart) {
                $matchingPart->plannedDeliveryDate = $salesOrderLine->plannedDeliveryDate;
                $matchingPart->shippingOrigin = $siteToLocationConverter->getLocationFromSite($salesOrderLine->site);
            }
        }
    }

    public function isFullyShipped(): bool
    {
        return $this->getParts()->filter(static fn (SparePartsRequestPart $part) => !$part->isFullyShipped())->isEmpty();
    }

    private function addPartOrIncrementQuantity(SparePartsRequestPart $part): self
    {
        $matchingParts = $this->parts->filter(static function (SparePartsRequestPart $existingPart) use ($part) {
            return $part->partNumber === $existingPart->partNumber
                && (
                    (null === $existingPart->deletedAt && null === $part->deletedAt)
                    || (null !== $existingPart->deletedAt && null !== $part->deletedAt)
                )
            ;
        });

        if (!$matchingParts->isEmpty()) {
            /** @var SparePartsRequestPart $foundPart */
            $foundPart = $matchingParts->first();
            $foundPart->quantity += $part->quantity;
            $part->sparePartsRequest->fullyRemovePart($part);

            return $this;
        }

        $this->addPart($part);

        return $this;
    }
}
