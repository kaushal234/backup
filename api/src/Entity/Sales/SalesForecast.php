<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\NumericFilter;
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
use App\AI\Dto\SummaryOutput;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Controller\Sales\SalesForecast\SalesForecastNotifyController;
use App\Controller\Sales\SalesForecast\SalesForecastTransferController;
use App\Controller\Sales\SalesForecast\SalesForecastUnlinkController;
use App\Controller\Sales\SalesForecast\SalesForecastUpdateStatusController;
use App\DataProcessor\Sales\SalesForecast\SalesForecastLinkToDataProcessor;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Exclude;
use App\Doctrine\Mapping\Attributes\LoggedDate;
use App\Doctrine\Mapping\Attributes\LoggedName;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Dto\Sales\SalesForecast\SalesForecastLinkTo;
use App\Entity\Common\Airport;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EmissionRating;
use App\Filter\ColumnsFilter;
use App\Filter\SalesForecast\SalesForecastChildrenHierarchyFilter;
use App\Filter\SalesForecast\SalesForecastCustomerFilter;
use App\Filter\SalesForecast\SalesForecastHotDealsFilter;
use App\Filter\SalesForecast\SalesForecastMilitaryFilter;
use App\Filter\SalesForecast\SalesForecastOpenFilter;
use App\Filter\SimpleSearchFilter;
use App\Serializer\Filter\ContextFilter;
use App\Serializer\Filter\PropertyFilter;
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

#[ORM\Entity(repositoryClass: 'App\Repository\Sales\SalesForecastRepository')]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'csv', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            paginationFetchJoinCollection: true,
            normalizationContext: ['groups' => ['sales_forecast', 'expose_legacy', 'location_public', 'product_list', 'people_public', 'customer_public', 'customer:status', 'iata_code', 'emission_rating', 'quote:light']],
        ),
        new GetCollection(
            uriTemplate: '/sales_forecasts_my_area',
            paginationFetchJoinCollection: true,
            normalizationContext: ['groups' => ['sales_forecast', 'expose_legacy', 'location_public', 'product_list', 'people_public', 'customer_public', 'customer:status', 'iata_code', 'emission_rating', 'quote:light']],
            name: 'my_area',
        ),
        new Delete(
            uriTemplate: '/sales_forecasts/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'salesForecastFiles', fromClass: SalesForecastFile::class),
                'id' => new Link(fromClass: SalesForecast::class),
            ],
            defaults: ['parentProperty' => 'salesForecast', 'class' => SalesForecastFile::class],
            controller: DeleteController::class,
            security: "is_granted('SALES_FORECAST_EDIT_VOTER', object)",
            name: 'delete_sales_forecast_file',
        ),
        new Delete(security: "is_granted('FEATURE_SALES_FORECAST_ADMIN_EDIT', object) or is_granted('MOO_SFR')"),
        new Get(security: "is_granted('SALES_FORECAST_ACCESS_VOTER', object)"),
        new Get(
            uriTemplate: '/sales_forecasts/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'salesForecastFiles', fromClass: SalesForecastFile::class),
                'id' => new Link(fromClass: SalesForecast::class),
            ],
            defaults: ['parentProperty' => 'salesForecast', 'class' => SalesForecastFile::class],
            controller: DownloadController::class,
            security: "is_granted('SALES_FORECAST_ACCESS_VOTER', object)",
            name: 'download_sales_forecast_file',
        ),
        new Get(
            uriTemplate: '/ai/summarize/sales_forecasts/{id}',
            routePrefix: '',
            requirements: ['id' => '[1-9]\d*'],
            openapi: new Operation(
                summary: 'Request a summary for any SFR',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        required: true,
                        schema: ['type' => 'integer'],
                    ),
                ],
            ),
            normalizationContext: ['groups' => ['summary']],
            output: SummaryOutput::class,
            name: 'summarize_sfr',
            provider: SummarizeDataProvider::class
        ),
        new Post(
            uriTemplate: '/sales_forecasts/transfer',
            controller: SalesForecastTransferController::class,
            security: "is_granted('FEATURE_SALES_FORECAST_TRANSFER')",
            deserialize: false,
            name: 'transfer_sales_forecast',
        ),
        new Put(
            securityPostDenormalize: "is_granted('SALES_FORECAST_EDIT_VOTER', object)",
            validationContext: ['groups' => ['Default', 'sales_forecast_edit']]
        ),
        new Put(
            uriTemplate: '/sales_forecasts/{id}/unlink',
            controller: SalesForecastUnlinkController::class,
            security: "is_granted('SALES_FORECAST_ACCESS_VOTER', object) and (is_granted('SALES_FORECAST_EDIT_VOTER', object) or is_granted('MOO_SFR'))",
            deserialize: false,
            name: 'unlink',
        ),
        new Post(
            uriTemplate: '/sales_forecasts/{id}/link',
            denormalizationContext: ['groups' => ['sales_forecast:link']],
            security: "is_granted('SALES_FORECAST_ACCESS_VOTER', object) and (is_granted('SALES_FORECAST_EDIT_VOTER', object) or is_granted('MOO_SFR'))",
            input: SalesForecastLinkTo::class,
            name: 'link',
            processor: SalesForecastLinkToDataProcessor::class,
        ),
        new Put(
            uriTemplate: '/sales_forecasts/{id}/status',
            controller: SalesForecastUpdateStatusController::class,
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
            denormalizationContext: ['groups' => ['sales_forecast_status_edit']],
            security: "is_granted('SALES_FORECAST_ACCESS_VOTER', object)",
            securityPostDenormalize: "is_granted('SALES_FORECAST_EDIT_VOTER', object) or is_granted('MOO_SFR') or is_granted('FEATURE_SALES_FORECAST_FORCE_STATUS')",
            validationContext: ['groups' => ['sales_forecast_edit']],
            deserialize: false,
            name: 'api_sales_forecast_update_status',
        ),
        new Put(
            uriTemplate: '/sales_forecasts/{id}/notify',
            controller: SalesForecastNotifyController::class,
            deserialize: false,
            name: 'api_sales_forecasts_notify_item',
        ),
        new Post(
            uriTemplate: '/sales_forecasts/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getSalesForecastFiles', 'class' => SalesForecastFile::class],
            controller: UploadController::class,
            security: "is_granted('SALES_FORECAST_EDIT_VOTER', object)",
            deserialize: false,
            name: 'upload_sales_forecast_file',
        ),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['sales_forecast_detail', 'expose_legacy', 'location_public', 'product_list', 'people_public', 'customer_public', 'customer:status', 'customer:watch', 'iata_code', 'emission_rating', 'file', 'network', 'currency', 'competitor_public', 'catalogue_family_list', 'incoterm', 'quote:light']],
    denormalizationContext: ['groups' => []],
)]
#[ORM\Table(name: 'sales_forecasts')]
#[ApiFilter(OrderFilter::class, properties: [
    'id',
    'legacyId',
    'asm.lastname',
    'factory.name',
    'sso.name',
    'buyer.name',
    'endUser.name',
    'thirdParty.name',
    'product.name',
    'quantity',
    'status',
    'airport.name',
    'country.name',
    'tier.name',
    'delinquent',
    'createdAt',
    'estimatedSaleDate',
    'successPercentage',
    'masterSalesForecast.id',
])]
#[ApiFilter(SalesForecastChildrenHierarchyFilter::class)]
#[ApiFilter(DateFilter::class, properties: ['updatedAt', 'closedAt', 'createdAt', 'estimatedSaleDate'])]
#[ApiFilter(NumericFilter::class, properties: ['customerSuccessPercentage', 'successPercentage'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id',
    'legacyId',
    'lastComment' => 'partial',
    'endUser.name' => 'partial',
    'buyer.name' => 'partial',
])]
#[ApiFilter(BooleanFilter::class, properties: ['delinquent'])]
#[ApiFilter(SalesForecastCustomerFilter::class)]
#[ApiFilter(SalesForecastHotDealsFilter::class)]
#[ApiFilter(SalesForecastOpenFilter::class)]
#[ApiFilter(SalesForecastMilitaryFilter::class)]
#[ApiFilter(ContextFilter::class)]
#[ApiFilter(SearchFilter::class, properties: [
    'masterSalesForecast' => 'exact',
    'status' => 'exact',
    'lastComment' => 'partial',
    'legacyId' => 'exact',
    'product' => 'exact',
    'product.family' => 'exact',
    'product.financeFamily' => 'exact',
    'product.family.productType' => 'exact',
    'sso' => 'exact',
    'sso.legacyId' => 'exact',
    'factory' => 'exact',
    'asm' => 'exact',
    'poster' => 'exact',
    'equoteId' => 'exact',
    'buyer' => 'exact',
    'buyer.customerTypes.name' => 'exact',
    'endUser' => 'exact',
    'endUser.customerTypes.name' => 'exact',
    'thirdParty' => 'exact',
    'airport' => 'exact',
    'country' => 'exact',
    'tier' => 'exact',
])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['sfr_export']])]
#[ApiFilter(PropertyFilter::class, arguments: ['whitelist' => ['id', 'createdAt', 'lastCommentedAt', 'status', 'lastComment', 'sso' => ['name', 'currency'], 'factory' => ['name'], 'asm', 'buyer' => ['name'], 'endUser' => ['name'], 'airport' => ['code'], 'product' => ['name'], 'quantity', 'price', 'margin', 'estimatedSaleDate', 'customerSuccessPercentage', 'totalSuccessPercentage', 'successPercentage', 'country' => ['name'], 'tier' => ['name']]])]
#[ApiFilter(ColumnsFilter::class)]
#[App\Loggable]
#[Legacy\Synchronize(table: 'sfr')]
class SalesForecast
{
    use LegacyIdentifierTrait;

    /**
     * @var string
     */
    final public const BUDGET = 'BUDGET';
    /**
     * @var string
     */
    final public const IN_PROGRESS = 'IN_PROGRESS';
    /**
     * @var string
     */
    final public const DELAYED = 'DELAYED';
    /**
     * @var array
     */
    final public const OPEN_STATUSES = [self::DELAYED, self::IN_PROGRESS, self::BUDGET];

    // closed statuses
    final public const ORDERED = 'ORDERED';
    final public const LOST = 'LOST';
    final public const ORDER_CANCELLED = 'ORDER_CANCELLED';
    final public const CANCELLED = 'CANCELLED';
    final public const PARTIAL = 'PARTIAL';

    /**
     * @var array
     */
    final public const CLOSED_STATUSES = [self::ORDERED, self::LOST, self::ORDER_CANCELLED, self::CANCELLED, self::PARTIAL];

    // percentage for reports
    final public const HOT_DEALS_PERCENTAGE = 60;
    final public const RESTRICTED_COMMENT_PREFIX = 'Restricted comment: ';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sfr_export'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\MasterSalesForecast', inversedBy: 'salesForecasts')]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write'])]
    #[Assert\NotNull]
    #[Legacy\Column(column: 'sfr_master_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?MasterSalesForecast $masterSalesForecast = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['sales_forecast_detail', 'sales_forecast_public', 'sfr_export'])]
    #[Legacy\Column(column: 'dt', transformer: DateTimeToString::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['sales_forecast_detail'])]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['sales_forecast_detail', 'sfr_export'])]
    #[Gedmo\Timestampable(on: 'update', field: 'lastComment')]
    private ?\DateTimeInterface $lastCommentedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['sales_forecast_detail', 'sales_forecast_public'])]
    #[Legacy\Column(column: 'dt_closed', transformer: DateTimeToString::class)]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: [self::ORDERED, self::LOST, self::CANCELLED, self::PARTIAL, self::ORDER_CANCELLED])]
    private ?\DateTimeInterface $closedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $closureNotificationSentAt = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_write', 'sales_forecast_restricted_edit', 'sales_forecast_admin_edit', 'sales_forecast_public', 'sfr_export'])]
    #[Assert\Choice(callback: 'getOpenStatuses', groups: ['sales_forecast_create'])]
    #[Legacy\Column(column: 'status')]
    private string $status;

    #[ORM\Column(name: 'last_comment', type: 'text', nullable: true)]
    #[Groups(['sales_forecast_detail', 'sfr_export'])]
    #[Exclude]
    private ?string $lastComment = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write', 'sales_forecast_public', 'sfr_export'])]
    #[Assert\NotNull]
    #[ValidLocation(sso: true)]
    #[Legacy\Column(column: 'sso_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private Location $sso;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_admin_edit', 'sales_forecast_factory_edit', 'sales_forecast_write', 'sales_forecast_public', 'sfr_export'])]
    #[Assert\NotNull]
    #[ValidLocation(factory: true)]
    #[Legacy\Column(column: 'erp_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private Location $factory;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_admin_edit', 'sales_forecast_write', 'sales_forecast_public', 'sfr_export'])]
    #[Assert\NotNull]
    #[Transferable(manager: 'manager.customer.representative')]
    #[Transferable(handler: 'handler.sfr.asm.advanced', manager: 'manager.sfr')]
    #[Legacy\Column(column: 'asm_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private People $asm;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['sales_forecast_detail', 'sales_forecast'])]
    #[Legacy\Column(column: 'init_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Gedmo\Blameable(on: 'create')]
    private People $poster;

    #[ORM\Column(name: 'equote_id', type: 'string', nullable: true)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write', 'sales_forecast_public'])]
    #[Legacy\Column(column: 'equote_id')]
    private ?string $equoteId = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[ORM\JoinColumn(nullable: true)]
    #[ApiProperty(fetchEager: true)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write', 'sales_forecast_public', 'sfr_export'])]
    #[Assert\NotNull]
    #[Legacy\Column(column: 'buyer_customer_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Legacy\Column(column: 'cust_nama', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    private ?Customer $buyer = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[ORM\JoinColumn(nullable: true)]
    #[ApiProperty(fetchEager: true)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write', 'sales_forecast_public', 'sfr_export'])]
    #[Legacy\Column(column: 'user_customer_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?Customer $endUser = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write'])]
    #[Legacy\Column(column: 'third_party_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?Customer $thirdParty = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Country')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\NotNull]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_restricted_edit', 'sales_forecast_admin_edit', 'sales_forecast_write', 'sfr_export'])]
    #[Legacy\Column(column: 'cust_ctry', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    private ?Country $country = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Common\Airport')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write', 'sfr_export'])]
    #[Legacy\Column(column: 'apc', transformer: ObjectToProperty::class, options: ['property' => 'code'])]
    private ?Airport $airport = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Product')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write', 'sales_forecast_public', 'sfr_export'])]
    #[Assert\NotNull]
    #[Legacy\Column(column: 'model', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    private ?Product $product = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write', 'sales_forecast_public', 'sfr_export'])]
    #[Assert\NotNull]
    #[Assert\Type('integer')]
    #[Legacy\Column(column: 'qty')]
    private int $quantity;

    #[ORM\Column(type: 'date', nullable: false)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write', 'sales_forecast_public', 'sfr_export'])]
    #[Assert\NotNull]
    #[LoggedDate(format: 'Y-m')]
    #[Legacy\Column(column: 'year_id', transformer: DateTimeToString::class, options: ['format' => 'Y'])]
    #[Legacy\Column(column: 'month_id', transformer: DateTimeToString::class, options: ['format' => 'n'])]
    private \DateTimeInterface $estimatedSaleDate;

    #[ORM\Column(type: 'smallint', nullable: false)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write', 'sfr_export'])]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(100)]
    #[LoggedName('sfr.customerSuccessPercentage')]
    #[Legacy\Column(column: 'cust_pur_pc')]
    private int $customerSuccessPercentage;

    #[ORM\Column(type: 'smallint', nullable: false)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write', 'sfr_export'])]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(100)]
    #[LoggedName('sfr.successPercentage')]
    #[Legacy\Column(column: 'tld_succ_pc')]
    private int $successPercentage;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\EmissionRating')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['sales_forecast', 'sales_forecast_detail', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write', 'sfr_export'])]
    #[Assert\NotNull]
    #[Legacy\Column(column: 'eng_tier', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    private ?EmissionRating $tier = null;

    #[ORM\Column(name: 'delinquent', type: 'boolean', nullable: false)]
    #[Groups(['sales_forecast_detail', 'sales_forecast'])]
    private bool $delinquent = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotNull(groups: ['sales_forecast_create'])]
    #[Assert\NotBlank(groups: ['sales_forecast_create'])]
    #[Groups(['sales_forecast_detail', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write', 'sfr_export'])]
    #[Legacy\Column(column: 'price')]
    private ?int $price = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(100)]
    #[Assert\NotNull(groups: ['sales_forecast_create'])]
    #[Assert\NotBlank(groups: ['sales_forecast_create'])]
    #[Groups(['sales_forecast_detail', 'sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_write', 'sfr_export'])]
    private ?float $margin = null;

    #[Assert\NotBlank(groups: ['sales_forecast_edit'])]
    #[Groups(['sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_factory_edit', 'sales_forecast_write', 'sales_forecast_status_edit', 'sales_forecast:notification'])]
    private ?string $comment = null;

    #[Groups(['sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_factory_edit', 'sales_forecast_status_edit'])]
    #[Assert\Type(type: 'boolean')]
    private bool $synchronized = false;

    #[Groups(['sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_factory_edit'])]
    #[Assert\Type(type: 'boolean')]
    private bool $notificationRestricted = false;

    #[Groups(['sales_forecast_admin_edit', 'sales_forecast_restricted_edit', 'sales_forecast_factory_edit'])]
    #[Assert\Type(type: 'boolean')]
    private bool $notifyPackage = true;

    /**
     * @var Collection<SalesForecastFile>
     */
    #[ORM\OneToMany(mappedBy: 'salesForecast', targetEntity: 'App\Entity\Sales\SalesForecastFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['sales_forecast_detail'])]
    private Collection $salesForecastFiles;

    /**
     * @var Collection<ForecastClosure>
     */
    #[ORM\OneToMany(mappedBy: 'salesForecast', targetEntity: 'App\Entity\Sales\ForecastClosure', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    #[Groups(['sales_forecast_detail'])]
    private Collection $forecastClosures;

    /**
     * @var Collection<SalesForecastSnapshot>
     */
    #[ORM\OneToMany(mappedBy: 'originalSalesForecast', targetEntity: 'App\Entity\Sales\SalesForecastSnapshot', cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $salesForecastSnapshots;

    #[ORM\ManyToOne(targetEntity: Quote::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['sales_forecast_detail', 'sales_forecast', 'sales_forecast_restricted_edit', 'sales_forecast_admin_edit', 'sales_forecast_write', 'sfr_export'])]
    private ?Quote $quote = null;

    public function __construct()
    {
        $this->forecastClosures = new ArrayCollection();
        $this->salesForecastFiles = new ArrayCollection();
        $this->salesForecastSnapshots = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMasterSalesForecast(): ?MasterSalesForecast
    {
        return $this->masterSalesForecast;
    }

    public function setMasterSalesForecast(MasterSalesForecast $masterSalesForecast): self
    {
        $this->masterSalesForecast = $masterSalesForecast;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTime $closedAt): self
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    public function getClosureNotificationSentAt(): ?\DateTimeInterface
    {
        return $this->closureNotificationSentAt;
    }

    public function setClosureNotificationSentAt(?\DateTime $closureNotificationSentAt): self
    {
        $this->closureNotificationSentAt = $closureNotificationSentAt;

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

    public function getLastComment(): ?string
    {
        return $this->lastComment;
    }

    public function setLastComment(string $lastComment): self
    {
        $this->lastComment = $lastComment;

        return $this;
    }

    public function getSso(): Location
    {
        return $this->sso;
    }

    public function setSso(Location $sso): self
    {
        $this->sso = $sso;

        return $this;
    }

    public function getFactory(): Location
    {
        return $this->factory;
    }

    public function setFactory(Location $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    public function getAsm(): People
    {
        return $this->asm;
    }

    public function setAsm(People $asm): self
    {
        $this->asm = $asm;

        return $this;
    }

    public function getPoster(): People
    {
        return $this->poster;
    }

    public function setPoster(People $poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    public function getEquoteId(): ?string
    {
        return $this->equoteId;
    }

    public function setEquoteId(?string $equoteId): self
    {
        $this->equoteId = $equoteId;

        return $this;
    }

    public function getBuyer(): ?Customer
    {
        return $this->buyer;
    }

    public function setBuyer(?Customer $buyer): self
    {
        $this->buyer = $buyer;

        return $this;
    }

    public function getEndUser(): ?Customer
    {
        return $this->endUser;
    }

    public function setEndUser(?Customer $endUser): self
    {
        $this->endUser = $endUser;

        return $this;
    }

    public function getThirdParty(): ?Customer
    {
        return $this->thirdParty;
    }

    public function setThirdParty(?Customer $thirdParty): self
    {
        $this->thirdParty = $thirdParty;

        return $this;
    }

    public function getAirport(): ?Airport
    {
        return $this->airport;
    }

    public function setAirport(?Airport $airport): self
    {
        $this->airport = $airport;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getEstimatedSaleDate(): \DateTimeInterface
    {
        return $this->estimatedSaleDate;
    }

    public function setEstimatedSaleDate(\DateTime $estimatedSaleDate): self
    {
        $this->estimatedSaleDate = $estimatedSaleDate->modify('last day of');

        return $this;
    }

    public function getCustomerSuccessPercentage(): int
    {
        return $this->customerSuccessPercentage;
    }

    public function setCustomerSuccessPercentage(int $customerSuccessPercentage): self
    {
        $this->customerSuccessPercentage = $customerSuccessPercentage;

        return $this;
    }

    public function getSuccessPercentage(): int
    {
        return $this->successPercentage;
    }

    public function setSuccessPercentage(int $successPercentage): self
    {
        $this->successPercentage = $successPercentage;

        return $this;
    }

    public function getTier(): ?EmissionRating
    {
        return $this->tier;
    }

    public function setTier(?EmissionRating $tier): self
    {
        $this->tier = $tier;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getMargin(): ?float
    {
        return $this->margin;
    }

    public function setMargin(float $margin): self
    {
        $this->margin = $margin;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Collection<ForecastClosure>
     */
    public function getForecastClosures(): Collection
    {
        return $this->forecastClosures;
    }

    public function addForecastClosure(ForecastClosure $forecastClosure): self
    {
        $this->forecastClosures->add($forecastClosure);

        return $this;
    }

    public function isDelinquent(): bool
    {
        return $this->delinquent;
    }

    public function setDelinquent(bool $delinquent): self
    {
        $this->delinquent = $delinquent;

        return $this;
    }

    public function removeForecastClosure(ForecastClosure $forecastClosure): self
    {
        $this->forecastClosures->removeElement($forecastClosure);

        return $this;
    }

    /**
     * @return Collection<SalesForecastFile>
     */
    public function getSalesForecastFiles(): Collection
    {
        return $this->salesForecastFiles;
    }

    public function addSalesForecastFile(SalesForecastFile $salesForecastFile): self
    {
        $salesForecastFile->setSalesForecast($this);
        $this->salesForecastFiles->add($salesForecastFile);

        return $this;
    }

    public function removeSalesForecastFile(SalesForecastFile $salesForecastFile): self
    {
        $this->salesForecastFiles->removeElement($salesForecastFile);

        return $this;
    }

    public function isNotificationRestricted(): bool
    {
        return $this->notificationRestricted;
    }

    public function setNotificationRestricted(bool $notificationRestricted): self
    {
        $this->notificationRestricted = $notificationRestricted;

        return $this;
    }

    public function isNotifyPackage(): bool
    {
        return $this->notifyPackage;
    }

    public function setNotifyPackage(bool $notifyPackage): self
    {
        $this->notifyPackage = $notifyPackage;

        return $this;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized;
    }

    public function setSynchronized(bool $synchronized): self
    {
        $this->synchronized = $synchronized;

        return $this;
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

    public function getLastCommentedAt(): ?\DateTimeInterface
    {
        return $this->lastCommentedAt;
    }

    public function setLastCommentedAt(?\DateTime $lastCommentedAt): self
    {
        $this->lastCommentedAt = $lastCommentedAt;

        return $this;
    }

    public function getOpenStatuses(): array
    {
        return self::OPEN_STATUSES;
    }

    public function getQuote(): ?Quote
    {
        return $this->quote;
    }

    public function setQuote(?Quote $quote): self
    {
        $this->quote = $quote;

        return $this;
    }
}
