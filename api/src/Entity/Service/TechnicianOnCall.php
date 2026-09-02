<?php

declare(strict_types=1);

namespace App\Entity\Service;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
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
use App\AI\DataProvider\Summarize\SummarizeDataProvider;
use App\AI\DataProvider\TechnicianOnCall\ClosureSuggestionDataProvider;
use App\AI\Dto\Service\ClosureSuggestionOutput;
use App\AI\Dto\SummaryOutput;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\DataProcessor\Service\BatchCreateTechnicianOnCallProcessor;
use App\DataProcessor\Service\TechnicianOnCallEmailDataProcessor;
use App\DataProvider\Service\TechnicianOnCallPublicAccessDataProvider;
use App\Doctrine\Mapping\Attributes\Audible;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Dto\Service\TechnicianOnCallDuplicateInput;
use App\Dto\Service\TechnicianOnCallDuplicateOutput;
use App\Dto\Service\TechnicianOnCallEmailInput;
use App\Entity\Activity\Comment;
use App\Entity\Common\Airport;
use App\Entity\ConfidentialInterface;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\IndiceFactor;
use App\Entity\Parts\TOCSparePartsRequest;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\CustomerServiceRecord\TechnicianOnCallCustomerServiceRecord;
use App\Entity\Service\TechnicianOnCall\TechnicianOnCallSurvey;
use App\Entity\Support\EquipmentRecord\HourMeterTransaction\TechnicianOnCallHourMeterTransaction;
use App\Entity\Support\UnitOperationalStatus;
use App\Entity\UpdatableStatusEntityInterface;
use App\Entity\User;
use App\Filter\ColumnsFilter;
use App\Filter\Service\TechnicianOnCallActorFilter;
use App\Filter\Service\TechnicianOnCallFactoryFlagRecentlyClosedFilter;
use App\Filter\Service\TechnicianOnCallLateFilter;
use App\Filter\Service\TechnicianOnCallSurveyThisMonthFilter;
use App\Filter\SimpleSearchFilter;
use App\Jira\Resources\IssueByKeyInterface;
use App\Repository\Service\TechnicianOnCallRepository;
use App\Util\DateUtil;
use App\Validator\Constraints as AppAssert;
use App\Validator\Constraints\Service\TechnicianOnCall\ExtranetUserLinkedToEquipmentRecord;
use App\Validator\Constraints\Service\TechnicianOnCall\ExtranetUserLinkedToTocCustomer;
use App\Validator\Constraints\ValidExtranetUser;
use App\Validator\GroupProvider\TechnicianOnCallGroupProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Blameable;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\Mapping\Annotation\Timestampable;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\BooleanToChar;
use LegacyBundle\Doctrine\Transformer\BooleanToInteger;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: TechnicianOnCallRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'csv', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            openapi: true,
            normalizationContext: [
                'groups' => [
                    'toc:read',
                    'equipment_record:collection',
                    'airport_list',
                    'file:light',
                    'customer_light',
                    'unit_operational_status_detail',
                    'location_public',
                    'people_public',
                    'service_activity',
                    'technician_on_call_type',
                    'tag',
                ],
            ],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
        ),
        new Get(
            openapi: true,
            security: "is_granted('ACCESS_PEOPLE') or is_granted('EQUIPMENT_ACCESS_VOTER', object.equipmentRecord)",
            name: 'technician_on_call_get_item',
        ),
        new Get(
            uriTemplate: '/technician_on_calls/{id}/from_token',
            security: "is_granted('PUBLIC_ACCESS')",
            name: 'technician_on_call_survey_get_from_token',
            provider: TechnicianOnCallPublicAccessDataProvider::class,
        ),
        new Get(
            uriTemplate: '/technician_on_calls/{id}/main_file/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'mainFiles', fromClass: TechnicianOnCallMainFile::class),
                'id' => new Link(fromClass: TechnicianOnCall::class),
            ],
            defaults: ['parentProperty' => 'technicianOnCall', 'class' => TechnicianOnCallMainFile::class],
            controller: DownloadController::class,
            openapi: new Operation(
                summary: 'Get TOC Main file',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        description: 'ID of the TOC',
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
            name: 'download_main_technician_on_call_file',
        ),
        new Get(
            uriTemplate: '/technician_on_calls/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: TechnicianOnCallFile::class),
                'id' => new Link(fromClass: TechnicianOnCall::class),
            ],
            defaults: ['parentProperty' => 'technicianOnCall', 'class' => TechnicianOnCallFile::class],
            controller: DownloadController::class,
            openapi: new Operation(
                summary: 'Get list of TOC files',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        description: 'ID of the TOC',
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
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
            name: 'download_technician_on_call_file',
        ),
        new Get(
            uriTemplate: '/ai/suggest-closure/technician_on_calls/{id}',
            requirements: ['id' => '[1-9]\d*'],
            openapi: new Operation(
                summary: 'Suggest closure comment for a TOC using AI',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        required: true,
                        schema: ['type' => 'integer', 'minimum' => 1],
                    ),
                ],
            ),
            normalizationContext: ['groups' => ['closure_suggestion']],
            output: ClosureSuggestionOutput::class,
            name: 'technician_on_call_suggest_closure',
            provider: ClosureSuggestionDataProvider::class,
        ),
        new Get(
            uriTemplate: '/ai/summarize/technician_on_calls/{id}',
            routePrefix: '',
            requirements: ['id' => '[1-9]\d*'],
            openapi: new Operation(
                summary: 'Request a summary of a TOC using AI',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        required: true,
                        schema: ['type' => 'integer', 'minimum' => 1],
                    ),
                ],
            ),
            normalizationContext: ['groups' => ['summary']],
            output: SummaryOutput::class,
            name: 'technician_on_call_summarize',
            provider: SummarizeDataProvider::class,
        ),
        new Post(
            openapi: true,
            security: "is_granted('ACCESS_PEOPLE') or is_granted('TOC_WRITE_VOTER')",
            validationContext: ['groups' => ['TechnicianOnCall', 'validation:toc:create']],
            name: 'technician_on_call_create',
        ),
        new Post(
            uriTemplate: '/technician_on_calls/{id}/duplicate',
            denormalizationContext: ['groups' => ['toc_write', 'equipment_record:service']],
            input: TechnicianOnCallDuplicateInput::class,
            output: TechnicianOnCallDuplicateOutput::class,
            name: 'technician_on_call_duplicate',
            processor: BatchCreateTechnicianOnCallProcessor::class,
        ),
        new Post(
            uriTemplate: '/technician_on_calls/{id}/main_file',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getMainFile', 'class' => TechnicianOnCallMainFile::class],
            controller: UploadController::class,
            openapi: new Operation(
                summary: 'Add / change main file',
            ),
            normalizationContext: [],
            deserialize: false,
            name: 'upload_main_technician_on_call_file',
        ),
        new Post(
            uriTemplate: '/technician_on_calls/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => TechnicianOnCallFile::class],
            controller: UploadController::class,
            openapi: new Operation(
                summary: 'Add a new file',
            ),
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
            deserialize: false,
            name: 'upload_technician_on_call_file',
        ),
        new Post(
            uriTemplate: '/technician_on_calls/{id}/email',
            input: TechnicianOnCallEmailInput::class,
            output: false,
            validate: false,
            name: 'technician_on_call_email',
            processor: TechnicianOnCallEmailDataProcessor::class
        ),
        new Delete(
            uriTemplate: '/technician_on_calls/{id}/main_file/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'mainFiles', fromClass: TechnicianOnCallMainFile::class),
                'id' => new Link(fromClass: TechnicianOnCall::class),
            ],
            defaults: ['parentProperty' => 'technicianOnCall', 'class' => TechnicianOnCallMainFile::class],
            controller: DeleteController::class,
            openapi: new Operation(
                summary: 'Delete main file',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        description: 'ID of the TOC',
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
            name: 'delete_main_technician_on_call_file',
        ),
        new Delete(
            uriTemplate: '/technician_on_calls/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: TechnicianOnCallFile::class),
                'id' => new Link(fromClass: TechnicianOnCall::class),
            ],
            defaults: ['parentProperty' => 'technicianOnCall', 'class' => TechnicianOnCallFile::class],
            controller: DeleteController::class,
            openapi: new Operation(
                summary: 'Delete a file',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        description: 'ID of the TOC',
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
            name: 'delete_technician_on_call_file'
        ),
        new Put(
            openapi: true,
            denormalizationContext: ['groups' => ['toc:write', 'toc:edit']],
            security: "is_granted('FEATURE_TECHNICIAN_ON_CALL_EDIT')",
            name: 'technician_on_call_edit'
        ),
        new Put(
            uriTemplate: '/technician_on_calls/{id}/status',
            denormalizationContext: ['groups' => ['toc:update_status']],
            name: 'technician_on_call_status',
        ),
        new Put(
            uriTemplate: '/technician_on_calls/{id}/request-technician',
            denormalizationContext: ['groups' => ['toc:request_technician']],
            security: "is_granted('FEATURE_CUSTOMER_SERVICE_RECORD_CREATE')",
            name: 'technician_on_call_request_technician',
        ),
        new Delete(openapi: true, security: "is_granted('FEATURE_TECHNICIAN_ON_CALL_DELETE')"),
    ],
    routePrefix: 'service',
    normalizationContext: ['groups' => self::ITEM_NORMALIZATION_GROUPS],
    denormalizationContext: ['groups' => ['toc:write']],
    extraProperties: [
        Comment::EXTRANET_USER_COMMENTABLE => true,
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id',
    'legacyId',
    'status',
    'equipmentRecord',
    'airport',
    'airport.country',
    'airport.serviceAreas',
    'assignee',
    'technician',
    'unitOperationalStatus',
    'technicianOnCallType',
    'serviceActivity',
    'indiceFactor',
    'tags',
    'createdBy',
    'salesOrganisationService',
    'equipmentRecord.product.family.productType',
    'equipmentRecord.product',
    'equipmentRecord.salesOrganisation',
    'equipmentRecord.manufacturerLocation',
    'equipmentRecord.serialNumber',
    'factoryFlag',
    'equipmentRecord.buyer',
    'equipmentRecord.endUser',
    'equipmentRecord.maintainer',
    'parts.partNumber',
    'sparePartsRequests.parts.partNumber',
    'equipmentRecord.emissionRating',
    'title' => 'partial',
    'errorCodes' => 'partial',
    'thirdPartyRef',
])]
#[ApiFilter(BooleanFilter::class, properties: ['confidential'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt' => 'exact', 'solvedAt' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: [
    'id',
    'title',
    'errorCodes',
    'description',
    'airport.code',
    'airport.country',
    'indiceFactor',
    'createdAt',
    'updatedAt',
    'equipmentRecord.serialNumber',
    'salesOrganisationService.name',
    'assignee.lastname',
    'createdBy.lastname',
    'technician.lastname',
    'status',
    'serviceActivity.name',
    'technicianOnCallType.name',
    'unitOperationalStatus.name',
    'factoryFlag',
    'equipmentRecord.buyer',
    'equipmentRecord.endUser',
    'equipmentRecord.maintainer',
    'parts.partNumber',
    'sparePartsRequests.parts.partNumber',
    'customer.name',
])]
#[ApiFilter(GroupFilter::class, arguments: [
    'parameterName' => 'normalizationGroups',
    'overrideDefaultGroups' => false,
    'whitelist' => [
        'workflow',
        'equipment_record',
        'extranet_user',
        'user_profile',
        'user_profile:detail',
        'country',
        'extranet_user_detail',
        'address',
        'toc:read:detail',
        'expose_legacy',
        'equipment_serial',
        'tracking',
    ]])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id' => 'exact',
    'title' => 'partial',
    'airport.code' => 'partial',
    'indiceFactor' => 'partial',
    'equipmentRecord.serialNumber' => 'partial',
    'salesOrganisationService.name' => 'partial',
    'assignee.lastname' => 'partial',
    'technician.lastname' => 'partial',
    'status' => 'partial',
    'serviceActivity.name' => 'partial',
    'technicianOnCallType.name' => 'partial',
    'unitOperationalStatus.name' => 'partial',
])]
#[ApiFilter(TechnicianOnCallLateFilter::class)]
#[ApiFilter(TechnicianOnCallFactoryFlagRecentlyClosedFilter::class)]
#[ApiFilter(TechnicianOnCallSurveyThisMonthFilter::class)]
#[ApiFilter(ColumnsFilter::class)]
#[ApiFilter(TechnicianOnCallActorFilter::class)]
#[SoftDeleteable]
#[Loggable]
#[Legacy\Synchronize(table: 'toc')]
#[Audible(type: 'technician_on_call')]
#[AppAssert\Service\IndiceFactorUnitOperationalStatus]
#[AppAssert\Service\TechnicianOnCall\HasSerial]
#[Assert\GroupSequenceProvider(provider: TechnicianOnCallGroupProvider::class)]
class TechnicianOnCall implements UpdatableStatusEntityInterface, LegacyIdInterface, ConfidentialInterface, IssueByKeyInterface
{
    use LegacyIdentifierTrait;

    final public const ITEM_NORMALIZATION_GROUPS = [
        'toc:read',
        'toc:read:detail',
        'equipment_record:service',
        'emission_rating',
        'customer_light',
        'catalogue_product',
        'iata_code_detail',
        'location_public',
        'people_public',
        'people_photo',
        'expose_legacy',
        'unit_operational_status_detail',
        'technician_on_call_type',
        'service_activity',
        'tag',
        'file',
        'customer_service_record',
        'spare_parts_request',
        'part',
        'hour_meter_transaction:light',
        'extranet_user',
        'phone',
        'toc:survey',
    ];

    final public const string MODULE_NAME = 'TOC';
    final public const string PENDING = 'PENDING';
    final public const string IN_PROGRESS = 'IN_PROGRESS';
    final public const string SOLVED = 'SOLVED';
    final public const string CLOSED = 'CLOSED';
    final public const string SUSPENDED = 'SUSPENDED';

    final public const OPENED_STATUSES = [self::PENDING, self::IN_PROGRESS, self::SUSPENDED];
    final public const CLOSED_STATUSES = [self::CLOSED, self::SOLVED];

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['toc:read', 'toc:read:detail', 'trouble_ticket:reminder'])]
    public ?string $jiraTracteasyIssueKey = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['toc:read', 'toc:read:detail', 'toc:write'])]
    #[Legacy\Column(column: 'notification', transformer: BooleanToChar::class, options: ['trueValue' => 'N', 'falseValue' => 'Y'])]
    public bool $confidential = false;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['toc:read'])]
    #[Legacy\Column(column: 'short_desc')]
    public string $title;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    // Min length only for TOC created since migration
    #[Assert\When(
        expression: 'this.isAfterReleaseDate()',
        constraints: [
            new Assert\Length(min: 12),
        ]
    )]
    #[Groups(['toc:read', 'toc:read:detail', 'toc:write'])]
    public string $originalTitle;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['toc:read'])]
    #[Legacy\Column(column: 'prob_dsca')]
    public string $description;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'string')]
    // Min length only for TOC created since migration
    #[Assert\When(
        expression: 'this.isAfterReleaseDate()',
        constraints: [
            new Assert\Length(min: 12),
        ]
    )]
    #[Groups(['toc:read', 'toc:read:detail', 'toc:write'])]
    public string $originalDescription;

    #[ORM\Column(length: 50)]
    #[Groups(['toc:read', 'toc:write', 'toc:update_status'])]
    #[Legacy\Column(column: 'status')]
    public string $status = self::PENDING;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Timestampable(on: 'create')]
    #[Groups(['toc:read'])]
    #[Legacy\Column(column: 'dt', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    public \DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['toc:read:detail'])]
    #[Legacy\Column(column: 'dt_closed', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    public ?\DateTime $solvedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Timestampable(on: 'update')]
    #[Groups(['toc:read'])]
    public ?\DateTime $updatedAt = null;

    #[ORM\Column(name: 'deleted_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['toc:read:detail'])]
    public ?\DateTimeInterface $deletedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Blameable(on: 'create')]
    #[Groups(['toc:read'])]
    #[Legacy\Column(column: 'postid', transformer: ObjectToProperty::class, options: ['property' => 'legacy_id'])]
    public ?User $createdBy = null;

    #[ORM\ManyToOne(targetEntity: EquipmentRecord::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['toc:read', 'toc:write'])]
    #[ApiProperty(fetchEager: false)]
    #[Legacy\Column(column: 'erid', transformer: ObjectToProperty::class, options: ['property' => 'legacy_id'])]
    #[Assert\Expression(
        expression: 'value === null or value.getState() !== "RETIRED"',
        message: 'technician_on_call.messages.errors.equipment_record_retired',
        groups: ['validation:toc:create'],
    )]
    #[Assert\Expression(
        expression: 'value === null or value.getDateShipped() !== null',
        message: 'technician_on_call.messages.errors.equipment_record_not_shipped',
        groups: ['validation:toc:create'],
    )]
    public ?EquipmentRecord $equipmentRecord = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['toc:read', 'toc:write'])]
    public ?string $serialNumber = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[Groups(['toc:read', 'toc:write'])]
    #[Legacy\Column(column: 'assid', transformer: ObjectToProperty::class, options: ['property' => 'legacy_id'])]
    public ?People $assignee = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[Groups(['toc:read', 'toc:write'])]
    #[Legacy\Column(column: 'tecid', transformer: ObjectToProperty::class, options: ['property' => 'legacy_id'])]
    #[AppAssert\HasGroup(roles: ['GG_SERVICE', 'GG_SERVICE_AGENTS'])]
    public ?People $technician = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['toc:read', 'toc:write'])]
    #[Legacy\Column(column: 'error_codes')]
    public ?string $errorCodes = null;

    #[ORM\ManyToOne(targetEntity: UnitOperationalStatus::class)]
    #[Groups(['toc:read', 'toc:write'])]
    #[Legacy\Column(column: 'unit_operation_status', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    public ?UnitOperationalStatus $unitOperationalStatus = null;

    #[ORM\ManyToOne(targetEntity: TechnicianOnCallType::class)]
    #[Groups(['toc:read', 'toc:write'])]
    #[Assert\NotBlank]
    #[AppAssert\Service\TechnicianOnCall\HasWarranty]
    #[Legacy\Column(column: 'toc_type', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    public TechnicianOnCallType $technicianOnCallType;

    #[ORM\ManyToOne(targetEntity: ServiceActivity::class)]
    #[Groups(['toc:read', 'toc:write'])]
    #[Assert\NotBlank]
    #[Legacy\Column(column: 'activity_type', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    public ServiceActivity $serviceActivity;

    #[ORM\Column]
    #[Assert\Choice(callback: [IndiceFactor::class, 'values'])]
    #[Assert\NotBlank]
    #[Groups(['toc:read', 'toc:write'])]
    #[Legacy\Column(column: 'ifactor')]
    public string $indiceFactor;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['toc:read:detail'])]
    public ?string $symptoms = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\When(
        expression: 'this.getStatus() in constant("App\\\Entity\\\Service\\\TechnicianOnCall::CLOSED_STATUSES")',
        constraints: [new Assert\NotBlank()],
    )]
    #[ApiProperty(
        description: 'Required when status changed to solved',
    )]
    #[Groups(['toc:read:detail', 'toc:write', 'toc:update_status'])]
    public ?string $originalSymptoms = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['toc:read:detail'])]
    public ?string $rootCause = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\When(
        expression: 'this.getStatus() in constant("App\\\Entity\\\Service\\\TechnicianOnCall::CLOSED_STATUSES")',
        constraints: [new Assert\NotBlank()],
    )]
    #[ApiProperty(
        description: 'Required when status changed to solved',
    )]
    #[Groups(['toc:read:detail', 'toc:write', 'toc:update_status'])]
    public ?string $originalRootCause = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['toc:read:detail'])]
    public ?string $solution = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\When(
        expression: 'this.getStatus() in constant("App\\\Entity\\\Service\\\TechnicianOnCall::CLOSED_STATUSES")',
        constraints: [new Assert\NotBlank()],
    )]
    #[ApiProperty(
        description: 'Required when status changed to solved',
    )]
    #[Groups(['toc:read:detail', 'toc:write', 'toc:update_status'])]
    public ?string $originalSolution = null;

    #[ORM\ManyToOne(targetEntity: Airport::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['toc:read', 'toc:write'])]
    #[Legacy\Column(column: 'apc', transformer: ObjectToProperty::class, options: ['property' => 'code'])]
    public Airport $airport;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['toc:read', 'toc:write'])]
    #[Legacy\Column(column: 'ssoid', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public ?Location $salesOrganisationService = null;

    #[ORM\OneToMany(targetEntity: TechnicianOnCallCustomerServiceRecord::class, mappedBy: 'technicianOnCall', orphanRemoval: true)]
    #[Groups(['toc:read:detail'])]
    #[ApiProperty(fetchEager: false)]
    #[AppAssert\Service\TechnicianOnCall\CustomerServiceRecordExist]
    public Collection $customerServiceRecords;

    #[Groups(['toc:write', 'toc:request_technician'])]
    #[Assert\When(
        expression: 'this.getStatus() in constant("App\\\Entity\\\Service\\\TechnicianOnCall::CLOSED_STATUSES")',
        constraints: [new Assert\IsNull(message: 'technician_on_call.customer_service_record.creation')],
    )]
    #[Assert\When(
        expression: '!this.customerServiceRecords.isEmpty()',
        constraints: [new Assert\IsNull(message: 'technician_on_call.customer_service_record.already_exist')],
    )]
    public ?NestedTechnicianOnCallCustomerServiceRecord $nestedCustomerServiceRecord = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['toc:read', 'toc:read:detail'])]
    #[Legacy\Column(column: 'factory_support_flag', transformer: BooleanToInteger::class)]
    #[AppAssert\Service\TechnicianOnCall\FactoryFlag(groups: ['factory_flag'])]
    public bool $factoryFlag = false;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['toc:read', 'toc:write'])]
    #[ApiProperty(fetchEager: false)]
    #[Legacy\Column(column: 'cuid', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public ?Customer $customer = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['toc:read:detail', 'toc:write'])]
    #[Legacy\Column(column: 'third_party', transformer: BooleanToChar::class, options: ['trueValue' => 'Y', 'falseValue' => 'N'])]
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\When(
        expression: 'this.thirdPartyRef !== null',
        constraints: [new Assert\NotNull(message: 'technician_on_call.messages.errors.third_party_name_required_with_third_party')],
    )]
    public ?string $thirdPartyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['toc:read', 'toc:read:detail', 'toc:write'])]
    #[Assert\NotBlank(allowNull: true)]
    public ?string $thirdPartyRef = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\When(
        expression: 'this.getStatus() in constant("App\\\Entity\\\Service\\\TechnicianOnCall::CLOSED_STATUSES") and this.thirdPartyName !== null',
        constraints: [
            new Assert\GreaterThanOrEqual(0),
            new Assert\NotNull(),
        ],
    )]
    #[ApiProperty(description: 'Required when status changed to solved if a thirdPartyName is filled')]
    #[Groups(['toc:read:detail', 'toc:update_status'])]
    public ?int $thirdPartyHours = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\When(
        expression: 'this.getStatus() in constant("App\\\Entity\\\Service\\\TechnicianOnCall::CLOSED_STATUSES") and this.thirdPartyName !== null',
        constraints: [new Assert\NotBlank()],
    )]
    #[ApiProperty(description: 'Required when status changed to solved if a thirdPartyName is filled')]
    #[Groups(['toc:read:detail', 'toc:update_status'])]
    public ?string $thirdPartyJobDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['toc:read:detail', 'toc:write'])]
    #[Legacy\Column(column: 'warranty_id')]
    public ?int $warrantyLegacyId = null;

    #[Groups(['toc:read:detail', 'toc:write'])]
    public ?int $hourMeter = null;

    #[ApiProperty(fetchEager: false)]
    #[ORM\OneToOne(targetEntity: TechnicianOnCallSurvey::class, mappedBy: 'technicianOnCall')]
    #[Groups(['toc:read:detail'])]
    public ?TechnicianOnCallSurvey $survey = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['toc:read:detail'])]
    public ?string $token = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['toc:read'])]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: TechnicianOnCallTag::class, mappedBy: 'technicianOnCalls')]
    #[Groups(['toc:read', 'toc:write'])]
    private Collection $tags;

    #[ApiProperty(fetchEager: false)]
    #[ORM\ManyToOne(targetEntity: ExtranetUser::class)]
    #[Groups(['toc:read', 'toc:write'])]
    #[Assert\When(
        expression: 'this.getStatus() in constant("App\\\Entity\\\Service\\\TechnicianOnCall::OPENED_STATUSES")',
        constraints: [new ValidExtranetUser(), new ExtranetUserLinkedToTocCustomer()],
        groups: ['contacts'],
    )]
    #[Legacy\Column(column: 'conid', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?ExtranetUser $mainContact = null;

    #[ORM\ManyToMany(targetEntity: ExtranetUser::class)]
    #[Groups(['toc:read:detail', 'toc:write'])]
    #[Assert\When(
        expression: 'this.getStatus() in constant("App\\\Entity\\\Service\\\TechnicianOnCall::OPENED_STATUSES")',
        constraints: [new Assert\All(
            constraints: [new ValidExtranetUser(), new ExtranetUserLinkedToEquipmentRecord()],
            groups: ['contacts'],
        )],
        groups: ['contacts'],
    )]
    private Collection $contacts;

    #[ORM\OneToMany(targetEntity: TechnicianOnCallMainFile::class, mappedBy: 'technicianOnCall', cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[Assert\Count(max: 1)]
    private Collection $mainFiles;

    #[ORM\OneToMany(targetEntity: TechnicianOnCallFile::class, mappedBy: 'technicianOnCall', cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $files;

    #[ORM\OneToMany(targetEntity: TechnicianOnCallPart::class, mappedBy: 'technicianOnCall', cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[Groups(['toc:read:detail', 'toc:update_status'])]
    private Collection $parts;

    #[ORM\OneToMany(targetEntity: TOCSparePartsRequest::class, mappedBy: 'technicianOnCall', fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[Groups(['toc:read:detail'])]
    private Collection $sparePartsRequests;

    #[ORM\OneToMany(targetEntity: TechnicianOnCallDefectivePart::class, mappedBy: 'technicianOnCall', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[Groups(['toc:read:detail', 'toc:update_status'])]
    #[Assert\Valid]
    #[Assert\When(
        expression: 'this.getStatus() not in constant("App\\\Entity\\\Service\\\TechnicianOnCall::CLOSED_STATUSES") and this.getDefectiveParts().count() !== 0',
        constraints: [
            new Assert\Count(
                min: 0,
                max: 0,
                exactMessage: 'technician_on_call.defective_parts.not_close'
            ),
        ],
    )]
    private Collection $defectiveParts;

    #[ORM\OneToMany(targetEntity: TechnicianOnCallHourMeterTransaction::class, mappedBy: 'technicianOnCall')]
    #[Groups(['toc:read:detail', 'hour_meter_transaction:light'])]
    #[ApiProperty(fetchEager: false)]
    private Collection $hourMeterTransactions;

    public function __construct()
    {
        $this->indiceFactor = IndiceFactor::IF_10->value;
        $this->tags = new ArrayCollection();
        $this->mainFiles = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->parts = new ArrayCollection();
        $this->sparePartsRequests = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->hourMeterTransactions = new ArrayCollection();
        $this->customerServiceRecords = new ArrayCollection();
        $this->defectiveParts = new ArrayCollection();
    }

    public function __clone(): void
    {
        $this->confidential = false;
        $this->serialNumber = null;
        $this->tags = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->mainFiles = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->parts = new ArrayCollection();
        $this->sparePartsRequests = new ArrayCollection();
        $this->hourMeterTransactions = new ArrayCollection();
        $this->customerServiceRecords = new ArrayCollection();
        $this->setMainContact(null);
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->solvedAt = null;
        $this->warrantyLegacyId = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isConfidential(): bool
    {
        return $this->confidential;
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
     * @return Collection<TechnicianOnCallTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(TechnicianOnCallTag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $tag->addTechnicianOnCall($this);
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(TechnicianOnCallTag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $tag->removeTechnicianOnCall($this);
            $this->tags->removeElement($tag);
        }

        return $this;
    }

    public function getMainContact(): ?ExtranetUser
    {
        return $this->mainContact;
    }

    public function setMainContact(?ExtranetUser $mainContact): self
    {
        $this->mainContact = $mainContact;

        return $this;
    }

    /**
     * @return Collection<ExtranetUser>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(ExtranetUser $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
        }

        return $this;
    }

    public function removeContact(ExtranetUser $contact): self
    {
        if ($this->contacts->contains($contact)) {
            $this->contacts->removeElement($contact);
        }

        return $this;
    }

    /**
     * @return Collection<TechnicianOnCallMainFile>
     */
    public function getMainFiles(): Collection
    {
        return $this->mainFiles;
    }

    public function addMainFile(TechnicianOnCallMainFile $mainFile): self
    {
        if (!$this->mainFiles->contains($mainFile)) {
            $this->mainFiles->add($mainFile);
            $mainFile->setTechnicianOnCall($this);
        }

        return $this;
    }

    public function removeMainFile(TechnicianOnCallMainFile $mainFile): self
    {
        if ($this->mainFiles->contains($mainFile)) {
            $this->mainFiles->removeElement($mainFile);
        }

        return $this;
    }

    #[Groups(['toc:read'])]
    public function getMainFile(): ?TechnicianOnCallMainFile
    {
        if (0 === $this->mainFiles->count()) {
            return null;
        }

        return $this->mainFiles->first();
    }

    public function setMainFile(?TechnicianOnCallMainFile $mainFile): self
    {
        if (null === $mainFile) {
            $this->mainFiles = new ArrayCollection();

            return $this;
        }

        return $this->addMainFile($mainFile);
    }

    /**
     * @return Collection<TechnicianOnCallFile>
     */
    #[Groups(['toc:read:detail'])]
    public function getFiles()
    {
        return $this->files;
    }

    public function addFile(TechnicianOnCallFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setTechnicianOnCall($this);
        }

        return $this;
    }

    public function removeFile(TechnicianOnCallFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }

        return $this;
    }

    /**
     * @return Collection<TechnicianOnCallDefectivePart>
     */
    #[Groups(['toc:read:detail'])]
    public function getDefectiveParts(): Collection
    {
        return $this->defectiveParts;
    }

    public function addDefectivePart(TechnicianOnCallDefectivePart $defectivePart): self
    {
        if (!$this->defectiveParts->contains($defectivePart)) {
            $this->defectiveParts->add($defectivePart);
            $defectivePart->technicianOnCall = $this;
        }

        return $this;
    }

    public function removeDefectivePart(TechnicianOnCallDefectivePart $defectivePart): self
    {
        if ($this->defectiveParts->contains($defectivePart)) {
            $this->defectiveParts->removeElement($defectivePart);
        }

        return $this;
    }

    /**
     * @return Collection<TechnicianOnCallHourMeterTransaction>
     */
    public function getHourMeterTransactions(): Collection
    {
        return $this->hourMeterTransactions;
    }

    /**
     * @return Collection<TechnicianOnCallPart>
     */
    #[Groups(['toc:read:detail'])]
    public function getParts(): Collection
    {
        return $this->parts;
    }

    public function addPart(TechnicianOnCallPart $part): self
    {
        if (!$this->parts->contains($part)) {
            $this->parts->add($part);
            $part->technicianOnCall = $this;
        }

        return $this;
    }

    public function removePart(TechnicianOnCallPart $part): self
    {
        if ($this->parts->contains($part)) {
            $this->parts->removeElement($part);
        }

        return $this;
    }

    /**
     * @return Collection<TechnicianOnCallCustomerServiceRecord>
     */
    #[Groups(['toc:read:detail'])]
    public function getCustomerServiceRecords(): Collection
    {
        return $this->customerServiceRecords;
    }

    public function addCustomerServiceRecord(TechnicianOnCallCustomerServiceRecord $customerServiceRecord): self
    {
        if (!$this->customerServiceRecords->contains($customerServiceRecord)) {
            $this->customerServiceRecords->add($customerServiceRecord);
            $customerServiceRecord->setTechnicianOnCall($this);
        }

        return $this;
    }

    public function removeCustomerServiceRecord(TechnicianOnCallCustomerServiceRecord $customerServiceRecord): self
    {
        if ($this->customerServiceRecords->contains($customerServiceRecord)) {
            $this->customerServiceRecords->removeElement($customerServiceRecord);
        }

        return $this;
    }

    /**
     * @return Collection<TOCSparePartsRequest>
     */
    #[Groups(['toc:read:detail'])]
    public function getSparePartsRequests(): Collection
    {
        return $this->sparePartsRequests;
    }

    public function addSparePartsRequest(TOCSparePartsRequest $sparePartsRequest): self
    {
        if (!$this->sparePartsRequests->contains($sparePartsRequest)) {
            $this->sparePartsRequests->add($sparePartsRequest);
            $sparePartsRequest->technicianOnCall = $this;
        }

        return $this;
    }

    public function removeSparePartsRequest(TOCSparePartsRequest $sparePartsRequest): self
    {
        if ($this->sparePartsRequests->contains($sparePartsRequest)) {
            $this->sparePartsRequests->removeElement($sparePartsRequest);
        }

        return $this;
    }

    #[Groups(['toc:read'])]
    public function getOpenDays(): int
    {
        return DateUtil::diff($this->createdAt, $this->solvedAt)->days + 1;
    }

    #[Groups(['toc:read'])]
    public function getDaysWithoutActivity(): int
    {
        return DateUtil::diff($this->updatedAt, $this->solvedAt)->days + 1;
    }

    #[Groups(['toc:read', 'toc:read:detail'])]
    public function getDaysWithoutActivityStatus(): string
    {
        $daysWithoutActivity = $this->getDaysWithoutActivity();

        return match (true) {
            IndiceFactor::IF_1->value === $this->indiceFactor && $daysWithoutActivity >= 15,
            IndiceFactor::IF_10->value === $this->indiceFactor && $daysWithoutActivity >= 7,
            \in_array($this->indiceFactor, [IndiceFactor::IF_100->value, IndiceFactor::IF_1000->value], true) && $daysWithoutActivity >= 3 => TechnicianOnCallDaysWithoutActivityStatus::OUTDATED->name,
            default => TechnicianOnCallDaysWithoutActivityStatus::ON_TIME->name,
        };
    }

    #[Groups(['toc:read:detail'])]
    public function getIsOpen(): bool
    {
        return \in_array($this->status, self::OPENED_STATUSES, true);
    }

    #[Assert\Callback]
    public function assertCommissioningIsConfidential(ExecutionContextInterface $context): void
    {
        if (isset($this->serviceActivity) && ServiceActivity::COMMISSIONING === $this->serviceActivity->name && !$this->confidential) {
            $context
                ->buildViolation('toc.messages.errors.commissioning_must_be_confidential')
                ->setTranslationDomain('technician_on_call')
                ->atPath('confidential')
                ->addViolation()
            ;
        }
    }

    #[Assert\Callback(groups: ['factory_flag'])]
    public function assertFactoryFlag(ExecutionContextInterface $context): void
    {
        if (!\in_array($this->status, self::CLOSED_STATUSES, true)) {
            return;
        }

        if (!$this->factoryFlag) {
            return;
        }

        $context
            ->buildViolation('toc.messages.errors.factory_flag_on_close')
            ->setTranslationDomain('technician_on_call')
            ->atPath('factoryFlag')
            ->addViolation()
        ;
    }

    #[Groups(['toc:read:detail'])]
    public function hasOpenCustomerServiceRecords(): bool
    {
        /** @var TechnicianOnCallCustomerServiceRecord $customerServiceRecord */
        foreach ($this->customerServiceRecords as $customerServiceRecord) {
            if ($customerServiceRecord->hasOpenIntervention() || $customerServiceRecord->isOpen()) {
                return true;
            }
        }

        return false;
    }

    #[Groups(['toc:read:detail'])]
    public function getCurrentCustomerServiceRecord(): ?TechnicianOnCallCustomerServiceRecord
    {
        if (!$this->hasOpenCustomerServiceRecords()) {
            return null;
        }

        $openCsr = $this->customerServiceRecords->filter(static function (TechnicianOnCallCustomerServiceRecord $customerServiceRecord) {
            return $customerServiceRecord->hasOpenIntervention() || $customerServiceRecord->isOpen();
        });

        $criteria = Criteria::create()
            ->orderBy(['createdAt' => Order::Ascending])
            ->setMaxResults(1)
        ;

        return !$openCsr->matching($criteria)->isEmpty() ? $openCsr->matching($criteria)->first() : null;
    }

    #[Groups(['toc:read:detail'])]
    public function getNumberOfOpenCustomerServiceRecords(): int
    {
        $numberOpenCustomerServiceRecords = 0;

        foreach ($this->customerServiceRecords as $customerServiceRecord) {
            if ($customerServiceRecord->isClosed()) {
                continue;
            }

            ++$numberOpenCustomerServiceRecords;
        }

        return $numberOpenCustomerServiceRecords;
    }

    #[Groups(['toc:read:detail'])]
    public function getNumberOfCustomerServiceRecordFiles(): int
    {
        return array_sum(
            $this->customerServiceRecords
                ->map(static fn ($csr) => $csr->getFiles()->count())
                ->toArray()
        );
    }

    public function getProjectKey(): string
    {
        return $this->customer->getEasymileJiraProjectKey();
    }

    public function isAfterReleaseDate(): bool
    {
        // It's a new TOC, so created after the release
        if (!isset($this->id, $this->createdAt)) {
            return true;
        }

        return $this->createdAt >= new \DateTimeImmutable('2026-06-22');
    }
}
