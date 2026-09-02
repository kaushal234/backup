<?php

declare(strict_types=1);

namespace App\Entity\Service\CustomerServiceRecord;

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
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Entity\Common\Airport;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Support\EquipmentRecord\HourMeterTransaction\CustomerServiceRecordHourMeterTransaction;
use App\Entity\UpdatableStatusEntityInterface;
use App\Filter\ColumnsFilter;
use App\Filter\DiscrFilter;
use App\Filter\Service\CustomerServiceRecordPlanningFilter;
use App\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\Blameable;
use Gedmo\Mapping\Annotation\Timestampable;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'customer_service_record')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap([
    'default' => CustomerServiceRecord::class,
    'toc' => TechnicianOnCallCustomerServiceRecord::class,
    'sb' => ServiceBulletinCustomerServiceRecord::class,
    'commissioning' => CommissioningCustomerServiceRecord::class,
])]
#[ApiFilter(SearchFilter::class, properties: [
    'id',
    'status',
    'airport',
    'airport.country',
    'interventions.leader',
    'interventions.operators',
    'equipmentRecord',
    'interventions.status',
    'createdBy',
    'legacyId',
    'equipmentRecord.salesOrganisation',
    'equipmentRecord.salesOrganisationService.legacyId',
    'equipmentRecord.salesOrganisationService',
    'equipmentRecord.manufacturerLocation',
    'equipmentRecord.endUser',
    'equipmentRecord.product',
    'equipmentRecord.deliveredCountry',
    'equipmentRecord.product.family.productType',
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt' => 'exact', 'completedAt' => 'exact', 'closedAt' => 'exact'])]
#[ApiFilter(DiscrFilter::class)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'createdAt' => 'DESC', 'updatedAt' => 'DESC', 'airport.code' => 'ASC', 'interventions.leader.lastname'])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['customer_service_record_light']])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => ['workflow', 'file']])]
#[ApiFilter(CustomerServiceRecordPlanningFilter::class)]
#[Gedmo\SoftDeleteable]
#[ApiResource(
    shortName: 'customer_service_record',
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => ['customer_service_record', 'equipment_record', 'location_public', 'iata_code_detail', 'people_public', 'expose_legacy']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
        ),
        new Post(security: "is_granted('FEATURE_CUSTOMER_SERVICE_RECORD_CREATE')"),
        new Post(
            uriTemplate: '/customer_service_records/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => CustomerServiceRecordFile::class],
            controller: UploadController::class,
            openapi: new Operation(
                summary: 'Add a new file',
            ),
            deserialize: false,
            validate: false,
            name: 'upload_customer_service_record_file',
        ),
        new Get(),
        new Get(
            uriTemplate: '/customer_service_records/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: CustomerServiceRecordFile::class),
                'id' => new Link(fromClass: AbstractCustomerServiceRecord::class),
            ],
            defaults: ['parentProperty' => 'customerServiceRecord', 'class' => CustomerServiceRecordFile::class],
            controller: DownloadController::class,
            openapi: new Operation(
                summary: 'Get list of CSR files',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        description: 'ID of the CSR',
                        required: true,
                        schema: ['type' => 'integer'],
                    ),
                    new Parameter(
                        name: 'fileId',
                        in: 'path',
                        description: 'ID of the file',
                        required: true,
                        schema: ['type' => 'integer'],
                    ),
                ],
            ),
            name: 'download_customer_service_record_file',
        ),
        new Put(
            denormalizationContext: ['groups' => ['customer_service_record:create', 'customer_service_record:update']],
            security: "is_granted('FEATURE_CUSTOMER_SERVICE_RECORD_EDIT')",
        ),
        new Delete(security: "is_granted('FEATURE_CUSTOMER_SERVICE_RECORD_DELETE')"),
        new Delete(
            uriTemplate: '/customer_service_records/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: CustomerServiceRecordFile::class),
                'id' => new Link(fromClass: AbstractCustomerServiceRecord::class),
            ],
            defaults: ['parentProperty' => 'customerServiceRecord', 'class' => CustomerServiceRecordFile::class],
            controller: DeleteController::class,
            openapi: new Operation(
                summary: 'Delete a file',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        description: 'ID of the CSR',
                        required: true,
                        schema: ['type' => 'integer'],
                    ),
                    new Parameter(
                        name: 'fileId',
                        in: 'path',
                        description: 'ID of the file',
                        required: true,
                        schema: ['type' => 'integer'],
                    ),
                ],
            ),
            security: "is_granted('FEATURE_CUSTOMER_SERVICE_RECORD_EDIT')",
            name: 'delete_customer_service_record_file',
        ),
    ],
    routePrefix: 'service',
    normalizationContext: [
        'groups' => self::NORMALIZATION_GROUP,
    ],
    denormalizationContext: [
        'groups' => ['customer_service_record:create', 'customer_service_record:detail'],
    ],
    openapi: true,
)]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => ['workflow']])]
#[ApiFilter(ColumnsFilter::class)]
#[AppAssert\WorkflowStatus]
#[Loggable]
#[Legacy\Synchronize(table: 'csr')]
#[Assert\When(
    expression: 'null === this.leader',
    constraints: [new AppAssert\Service\LeaderOnAssignedCustomerServiceRecords()],
)]
abstract class AbstractCustomerServiceRecord implements UpdatableStatusEntityInterface, LegacyIdInterface
{
    use LegacyIdentifierTrait;

    /** @var string */
    final public const PENDING = 'PENDING';
    /** @var string */
    final public const ASSIGNED = 'ASSIGNED';
    /** @var string */
    final public const IN_PROGRESS = 'IN-PROGRESS';
    /** @var string */
    final public const COMPLETED = 'COMPLETED';
    /** @var string */
    final public const CLOSED = 'CLOSED';
    /** @var string */
    final public const PLANNED = 'PLANNED';
    /** @var string[] */
    final public const OPEN_STATUSES = [self::PENDING, self::PLANNED, self::ASSIGNED];
    /** @var string[] */
    final public const CLOSED_STATUSES = [self::COMPLETED, self::CLOSED];

    final protected const NORMALIZATION_GROUP = ['customer_service_record:answer', 'customer_service_record', 'customer_service_record:detail', 'equipment_record', 'location_public', 'iata_code_detail', 'people_public', 'customer_light', 'expose_legacy', 'file', 'spare_parts_request', 'spare_parts_request:detail', 'part', 'legacy:service_bulletin', 'extranet_user', 'phone', 'hour_meter_transaction:light', 'toc:read', 'toc:read:detail'];

    #[ORM\Column(type: 'datetime')]
    #[Timestampable(on: 'create')]
    #[Groups(['customer_service_record'])]
    #[Legacy\Column(column: 'dt', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    public \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Timestampable(on: 'update')]
    #[Groups(['customer_service_record:detail'])]
    public ?\DateTime $updatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['customer_service_record:detail'])]
    public ?\DateTime $deletedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['customer_service_record', 'customer_service_record_light'])]
    #[Legacy\Column(column: 'dt_completed', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    #[Assert\When(
        expression: 'this.getStatus() == constant("App\\\Entity\\\Service\\\CustomerServiceRecord\\\AbstractCustomerServiceRecord::COMPLETED")',
        constraints: [
            new Assert\NotBlank(),
        ],
    )]
    public ?\DateTime $completedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Timestampable(on: 'change', field: 'status', value: self::CLOSED)]
    #[Groups(['customer_service_record:detail'])]
    #[Legacy\Column(column: 'dt_closed', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    #[Assert\When(
        expression: 'this.getStatus() == constant("App\\\Entity\\\Service\\\CustomerServiceRecord\\\AbstractCustomerServiceRecord::CLOSED")',
        constraints: [
            new Assert\NotBlank(),
        ],
    )]
    public ?\DateTime $closedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['customer_service_record', 'customer_service_record:detail', 'customer_service_record:update'])]
    #[Legacy\Column(column: 'dt_sche', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    #[Assert\When(
        expression: 'this.getStatus() == constant("App\\\Entity\\\Service\\\CustomerServiceRecord\\\AbstractCustomerServiceRecord::PLANNED")',
        constraints: [
            new Assert\NotBlank(),
        ],
    )]
    public ?\DateTime $plannedAt = null;

    #[ORM\Column(type: 'text', length: 255)]
    #[Groups(['customer_service_record', 'customer_service_record:create', 'customer_service_record_light'])]
    #[Legacy\Column(column: 'short_desc')]
    #[Assert\NotNull]
    public string $title = '';

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['customer_service_record', 'customer_service_record:create'])]
    #[Legacy\Column(column: 'int_desc')]
    public ?string $description;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[Blameable(on: 'create')]
    #[Groups(['customer_service_record'])]
    #[Legacy\Column(column: 'entered_by', transformer: ObjectToProperty::class, options: ['property' => 'legacy_id'])]
    public ?People $createdBy = null;

    #[Groups(['customer_service_record:create', 'customer_service_record:update'])]
    public ?People $leader = null;

    #[ORM\ManyToOne(targetEntity: EquipmentRecord::class)]
    #[Groups(['customer_service_record', 'customer_service_record:create'])]
    #[Assert\NotNull]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacy_id'])]
    #[Legacy\Column(column: 'sso_id', transformer: ObjectToProperty::class, options: ['property' => 'salesOrganisationService.legacyId'])]
    public EquipmentRecord $equipmentRecord;

    #[ORM\ManyToOne(targetEntity: ExtranetUser::class)]
    #[Groups(['customer_service_record:detail', 'extranet_user', 'customer_service_record:create', 'customer_service_record:update', 'phone'])]
    #[Legacy\Column(column: 'con_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public ?ExtranetUser $contact = null;

    /**
     * @var Collection<Intervention>
     */
    #[ORM\OneToMany(mappedBy: 'customerServiceRecord', targetEntity: Intervention::class, cascade: ['all'])]
    #[Groups(['customer_service_record', 'customer_service_record:detail'])]
    #[AppAssert\Service\UniqueOpenedIntervention]
    #[Assert\Valid]
    protected Collection $interventions;

    #[ORM\ManyToOne(targetEntity: Airport::class)]
    #[Groups(['customer_service_record', 'customer_service_record:create'])]
    #[Legacy\Column(column: 'apc', transformer: ObjectToProperty::class, options: ['property' => 'code'])]
    private ?Airport $airport = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['customer_service_record', 'customer_service_record:detail', 'customer_service_record:update'])]
    #[Legacy\Column(column: 'status')]
    private string $status = self::PENDING;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['customer_service_record', 'customer_service_record_light'])]
    private int $id;

    /**
     * @var Collection<CustomerServiceRecordFile>
     */
    #[ORM\OneToMany(mappedBy: 'customerServiceRecord', targetEntity: CustomerServiceRecordFile::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['customer_service_record:detail', 'customer_service_record:create', 'customer_service_record:update'])]
    private Collection $files;

    #[ORM\OneToMany(mappedBy: 'customerServiceRecord', targetEntity: CustomerServiceRecordHourMeterTransaction::class)]
    #[Groups(['customer_service_record:detail', 'hour_meter_transaction:light'])]
    private Collection $hourMeterTransactions;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<CustomerServiceRecordFile>
     */
    #[Groups(['customer_service_record'])]
    public function getFiles()
    {
        return $this->files;
    }

    public function addFile(CustomerServiceRecordFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setCustomerServiceRecord($this);
        }

        return $this;
    }

    public function removeFile(CustomerServiceRecordFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }

        return $this;
    }

    /**
     * @return Collection<CustomerServiceRecordHourMeterTransaction>
     */
    public function getHourMeterTransactions(): Collection
    {
        return $this->hourMeterTransactions;
    }

    public function addIntervention(Intervention $intervention): self
    {
        $this->interventions->add($intervention);
        $intervention->customerServiceRecord = $this;

        return $this;
    }

    /**
     * @return Collection<Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function hasOpenIntervention(): bool
    {
        return (bool) self::getOpenInterventionFromCollection($this->interventions);
    }

    #[Groups(['customer_service_record', 'customer_service_record:detail'])]
    public function getOpenIntervention(): ?Intervention
    {
        if ($this->hasOpenIntervention()) {
            return self::getOpenInterventionFromCollection($this->interventions);
        }

        return null;
    }

    public static function getOpenInterventionFromCollection(Collection $interventions): Intervention|bool
    {
        $interventions = $interventions->filter(static function (Intervention $intervention) {
            return $intervention->isOpen();
        });

        return $interventions->first();
    }

    #[Groups(['customer_service_record', 'customer_service_record:detail'])]
    public function hasToContinue(): bool
    {
        if (!$this->isOpen()) {
            return false;
        }

        if (0 === $this->interventions->count()) {
            return false;
        }

        $interventions = $this->interventions->filter(static function (Intervention $intervention) {
            return Intervention::TO_CONTINUE === $intervention->getStatus();
        });

        return 0 < $interventions->count();
    }

    #[Groups(['customer_service_record', 'customer_service_record:detail'])]
    public function hasClose(): bool
    {
        if (!$this->isOpen()) {
            return false;
        }

        if (0 === $this->interventions->count()) {
            return false;
        }

        $interventions = $this->interventions->filter(static function (Intervention $intervention) {
            return Intervention::FAILED_ASSIGNEE === $intervention->getStatus();
        });

        return 0 < $interventions->count();
    }

    public function isOpen(): bool
    {
        return \in_array($this->status, self::OPEN_STATUSES, true);
    }

    #[Groups(['customer_service_record', 'customer_service_record:detail'])]
    public function isClosed(?string $status = null): bool
    {
        return \in_array($status ?? $this->status, self::CLOSED_STATUSES, true);
    }

    abstract public function getLegacyModuleName(): string;

    abstract public function getLegacyModuleId(): ?int;

    public function getAirport(): ?Airport
    {
        return $this->airport;
    }

    public function setAirport(?Airport $airport): void
    {
        $this->airport = $airport;

        // Check if equipmentRecord is initialized before accessing it
        if (null !== $airport && isset($this->equipmentRecord)) {
            $this->equipmentRecord->setAirport($airport);
        }
    }
}
