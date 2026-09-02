<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\FreeTextQueryFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\Support\EquipmentRecord\EquipmentRecordLinkSynchronizationController;
use App\Controller\Support\EquipmentRecord\ExtranetQRCodeController;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Common\Airport;
use App\Entity\Directory\Location;
use App\Entity\Sales\Customer;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\Entity\Sales\Order;
use App\Entity\Sales\OrderToFactory;
use App\Entity\Sales\OrderTransaction;
use App\Entity\Sales\PreDeliveryInspection;
use App\Entity\Sales\Product;
use App\Entity\Support\EquipmentSerial;
use App\Entity\Support\Manual;
use App\Filter\ColumnsFilter;
use App\Filter\ExcludeFilter;
use App\Filter\NotBetweenFilter;
use App\Filter\SimpleSearchFilter;
use App\Filter\Support\EquipmentRecord\EquipmentRecordByCustomerFilter;
use App\Filter\Support\EquipmentRecord\EquipmentRecordByEndUserOrBuyerFilter;
use App\Filter\Support\EquipmentRecord\EquipmentRecordLateFilter;
use App\Filter\Support\EquipmentRecord\EquipmentRecordOdpFilter;
use App\Filter\Support\EquipmentRecord\EquipmentRecordShippedFilter;
use App\Link\DataTransformer\ArrayToLinkResource;
use App\Link\Mapping\Attributes\LinkField;
use App\Link\QueryBuilder\GraphQLMutationBuilder;
use App\Link\Resource\LinkResourceInterface;
use App\Link\Resource\LinkResourceTrait;
use App\Serializer\Filter\ContextFilter;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\BooleanToChar;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\EquipmentRecordRepository')]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'csv', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            openapi: true,
            normalizationContext: ['groups' => ['equipment_record', 'expose_legacy', 'location_public', 'equipment_serial', 'airport_list']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
            name: 'get_equipment_collection',
            parameters: [
                'autocomplete' => new QueryParameter(
                    filter: new FreeTextQueryFilter(new OrFilter(new PartialSearchFilter())),
                    description: 'To allow filtering by partial serialNumber or customerSerialNumber.',
                    properties: ['serialNumber', 'customerSerialNumber'],
                ),
            ],
        ),
        new GetCollection(
            uriTemplate: '/equipment_records_user',
            normalizationContext: ['groups' => ['equipment_record', 'expose_legacy']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
            name: 'get_by_enduser',
        ),
        new Put(
            denormalizationContext: [],
            security: "is_granted('EQUIPMENT_SERIAL_EDIT_VOTER', object.getManufacturerLocation())",
            forceEager: false,
        ),
        new Put(
            uriTemplate: 'equipment_records/{id}/extranet_update',
            denormalizationContext: ['groups' => ['equipment_record:extranet']],
            security: "is_granted('ACCESS_EXTRANET_USER') and is_granted('EQUIPMENT_ACCESS_VOTER', object)",
            forceEager: false,
        ),
        new Put(
            uriTemplate: 'equipment_records_odp/{id}',
            securityPostDenormalize: "is_granted('ODP_EQUIPMENT_RECORD_EDIT_VOTER', object)",
            forceEager: false,
            name: 'odp_er_edit',
        ),
        new Get(
            openapi: true,
            normalizationContext: ['groups' => ['equipment_record_detail', 'expose_legacy', 'location_public', 'equipment_serial', 'component', 'manual_public', 'people_public', 'emission_rating:detail', 'iata_code']],
            security: "is_granted('EQUIPMENT_ACCESS_VOTER', object)",
        ),
        new Get(
            uriTemplate: '/equipment_records/{id}/extranet_qrcode',
            controller: ExtranetQRCodeController::class,
            openapi: new Operation(
                summary: 'Get QRCode for equipment record to print on plate',
            ),
            output: false,
            read: false,
            name: 'equipment_record_extranet_qrcode',
        ),
        new Put(
            uriTemplate: '/equipment_records/{id}/link_synchronization',
            controller: EquipmentRecordLinkSynchronizationController::class,
            normalizationContext: ['groups' => ['equipment_record_detail', 'expose_legacy', 'location_public', 'equipment_serial', 'component', 'manual_public', 'people_public', 'emission_rating:detail']],
            denormalizationContext: ['groups' => [GraphQLMutationBuilder::LINK_NORMALIZATION_GROUP]],
            security: "is_granted('FEATURE_SYNCHRONIZE_LINK_EQUIPMENT_RECORD')",
            name: 'link_synchronization',
        ),
    ],
    normalizationContext: ['groups' => ['equipment_record_detail', 'expose_legacy', 'location_public', 'equipment_serial', 'component', 'manual_public', 'people_public']],
    denormalizationContext: ['groups' => ['odp:write']],
)]
#[ORM\Table(name: 'equipment_records')]
#[ApiFilter(OrderFilter::class, properties: [
    'type',
    'model',
    'serialNumber',
    'id',
    'customerSerialNumber',
    'product.name',
    'product.family.productType.englishName',
    'airport.code',
    'location',
    'endUser.name',
    'airport.country.name',
])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'serialNumber' => 'partial',
    'customerSerialNumber' => 'partial',
    'product.name' => 'partial',
    'product.family.productType.englishName' => 'partial',
    'airport.code',
])]
#[ApiFilter(DateFilter::class, properties: ['contracts.startDate', 'contracts.expirationDate', 'greenTagDate', 'estimatedGreenTagDate', 'firstGreenTagDate'])]
#[ApiFilter(NotBetweenFilter::class, properties: ['contracts.startDate;contracts.expirationDate' => NotBetweenFilter::INCLUDE_NULL])]
#[ApiFilter(ContextFilter::class)]
#[ApiFilter(EquipmentRecordByEndUserOrBuyerFilter::class, properties: ['buyer_or_endUser_name' => 'exact'])]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'legacyId' => 'exact',
    'endUser' => 'exact',
    'endUser.name' => 'exact',
    'maintainer' => 'exact',
    'buyer.name' => 'exact',
    'buyer' => 'exact',
    'buyer.mainSalesRepresentative.asm' => 'exact',
    'serialNumber' => 'exact',
    'airport' => 'exact',
    'airport.code' => 'exact',
    'airport.country.name' => 'exact',
    'contracts' => 'exact',
    'contracts.endUserRepresentatives' => 'exact',
    'contracts.buyerRepresentatives' => 'exact',
    'product.family.productType' => 'exact',
    'product' => 'exact',
    'model' => 'exact',
    'manufacturerLocation' => 'exact',
    'salesOrganisation' => 'exact',
    'salesOrganisation.legacyId' => 'exact',
    'type' => 'exact',
    'emissionRating' => 'exact',
    'combinationMode' => 'exact',
    'orderFactory.orderLine.legacyId' => 'exact',
    'orderFactory.orderLine.inspection' => 'exact',
    'product.family.productType.englishName' => 'exact',
    'product.name' => 'exact',
    'customerSerialNumber' => 'exact',
    'location' => 'exact',
])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups', 'overrideDefaultGroups' => false, 'whitelist' => ['equipment_record:service', 'iata_code_detail', 'odp:view', 'order_factory', 'order_line', 'order_transaction', 'publishable']])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['equipment_list', 'equipment_list_buyer', 'equipment_list_user', 'equipment_record_green_tag', 'equipment_record_esr_check']])]
#[ApiFilter(BooleanFilter::class, properties: ['light'])]
#[ApiFilter(ExcludeFilter::class)]
#[ApiFilter(EquipmentRecordShippedFilter::class)]
#[ApiFilter(EquipmentRecordLateFilter::class)]
#[ApiFilter(EquipmentRecordOdpFilter::class)]
#[ApiFilter(EquipmentRecordByCustomerFilter::class)]
#[ApiFilter(ColumnsFilter::class)]
#[App\Loggable]
#[Legacy\Synchronize(table: 'service')]
class EquipmentRecord implements \Stringable, LegacyIdInterface, LinkResourceInterface
{
    use LegacyIdentifierTrait;
    use LinkResourceTrait;
    public const COMBINATION_ER_COMBINED = 'ER COMBINED';
    public const COMBINATION_PRE_ASSEMBLY = 'PRE-ASSEMBLY';
    public const WARRANTY_STATUS_NOT_SHIPPED = 'NOT SHIPPED';
    public const WARRANTY_STATUS_EFFECTIVE = 'EFFECTIVE';
    public const WARRANTY_STATUS_MAYBE_EXPIRED = 'MAYBE EXPIRED';
    public const WARRANTY_STATUS_EXPIRED = 'EXPIRED';

    #[ORM\OneToOne(targetEntity: OrderToFactory::class, mappedBy: 'equipmentRecord')]
    #[Groups(['odp:view'])]
    public ?OrderToFactory $orderFactory = null;

    #[ORM\OneToOne(targetEntity: OrderTransaction::class, mappedBy: 'equipmentRecord')]
    #[Groups(['odp:view'])]
    public ?OrderTransaction $orderTransaction = null;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['equipment_record', 'equipment_record_detail', 'equipment_record_manuals', 'odp:view', 'equipment_record:service', 'equipment_record:collection'])]
    private int $id;

    #[ORM\Column(name: 'serial_number', type: 'string', length: 20, nullable: false)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(min: 1, max: 20)]
    #[ApiProperty(iris: ['https://schema.org/serialNumber'])]
    #[LinkField(fields: ['identifier', 'plateNumber', 'astusId'])]
    #[Groups(['equipment_record', 'equipment_list', 'equipment_record_detail', 'equipment_list_user', 'equipment_list_buyer', 'equipment_record_manuals', 'equipment_shipping_records', 'equipment_record_green_tag', 'equipment_record:service', 'equipment_record:collection', GraphQLMutationBuilder::LINK_NORMALIZATION_GROUP])]
    private string $serialNumber;

    #[ORM\OneToMany(targetEntity: EquipmentShippingRecordLine::class, mappedBy: 'equipmentRecord')]
    #[Groups(['equipment_record_notification'])]
    private Collection $equipmentShippingRecordLines;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer', inversedBy: 'equipmentRecordsAsBuyer')]
    #[Groups(['equipment_record', 'equipment_record_detail', 'equipment_list_buyer', 'equipment_record_manuals', 'odp:view', 'equipment_record:service'])]
    private ?Customer $buyer = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer', inversedBy: 'equipmentRecordsAsUser')]
    #[Groups(['equipment_record', 'equipment_record_detail', 'equipment_list_user', 'odp:view', 'equipment_record:service', 'equipment_record:collection'])]
    private ?Customer $endUser = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer', inversedBy: 'equipmentRecordsAsMaintainer')]
    #[Groups(['equipment_record', 'equipment_record_detail', 'equipment_record:service'])]
    private ?Customer $maintainer = null;

    #[ORM\Column(name: 'customer_serial_number', type: 'string', length: 30, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\AtLeastOneOf([new Assert\Blank(), new Assert\Length(min: 1, max: 30)])]
    #[ApiProperty(iris: ['https://schema.org/serialNumber'])]
    #[Groups(['equipment_record', 'equipment_record_detail', 'odp:view', 'equipment_record:extranet', 'equipment_record:service', GraphQLMutationBuilder::LINK_NORMALIZATION_GROUP])]
    #[Legacy\Column(column: 'cust_asset_num')]
    private ?string $customerSerialNumber = null;

    #[ORM\Column(name: 'model', type: 'string', length: 30, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\AtLeastOneOf([new Assert\Blank(), new Assert\Length(min: 1, max: 30)])]
    #[Groups(['equipment_record', 'equipment_list', 'equipment_record_detail', 'equipment_list_user', 'equipment_list_buyer', 'equipment_record_manuals', 'equipment_record_green_tag', 'equipment_record:service', 'equipment_record:collection'])]
    private ?string $model = null;

    #[ORM\Column(name: 'type', type: 'string', length: 50, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\AtLeastOneOf([new Assert\Blank(), new Assert\Length(min: 1, max: 50)])]
    #[Groups(['equipment_record', 'equipment_list', 'equipment_record_detail', 'equipment_list_user', 'equipment_list_buyer', 'equipment_record_manuals', 'equipment_record_green_tag', 'equipment_record:service', 'equipment_record:collection'])]
    private ?string $type = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Product', inversedBy: 'equipmentRecords')]
    #[Groups(['equipment_record', 'equipment_record_detail', 'equipment_record:service', GraphQLMutationBuilder::LINK_NORMALIZATION_GROUP])]
    #[LinkField(fields: ['equipmentModel'], transformer: ArrayToLinkResource::class, options: ['property' => 'name'])]
    private ?Product $product = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Common\Airport')]
    #[Groups(['equipment_record_detail', 'iata_code_detail', 'equipment_record:service', 'equipment_record:collection', 'equipment_record:extranet'])]
    #[Legacy\Column(column: 'airport_code', transformer: ObjectToProperty::class, options: ['property' => 'code'])]
    private ?Airport $airport = null;

    #[ORM\Column(name: 'location', type: 'string', length: 20, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\AtLeastOneOf([new Assert\Blank(), new Assert\Length(min: 1, max: 20)])]
    #[Groups(['equipment_record', 'equipment_record_detail', 'equipment_list_user', 'equipment_list_buyer', 'equipment_record_manuals', 'equipment_record:service'])]
    private ?string $location = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\EquipmentRecord', inversedBy: 'childrenEquipments')]
    #[Groups(['equipment_record_detail', 'equipment_record:service'])]
    #[MaxDepth(1)]
    private ?EquipmentRecord $parentEquipment = null;

    #[ORM\OneToMany(mappedBy: 'parentEquipment', targetEntity: 'App\Entity\EquipmentRecord')]
    #[Groups(['equipment_record_detail', 'equipment_record:service'])]
    #[MaxDepth(1)]
    private Collection $childrenEquipments;

    #[ORM\ManyToMany(targetEntity: 'App\Entity\Support\MaintenanceContract', mappedBy: 'equipmentRecords', fetch: 'EXTRA_LAZY')]
    private Collection $contracts;

    #[ORM\Column(name: 'date_shipped', type: 'datetime', nullable: true)]
    #[Groups(['equipment_record', 'equipment_record_detail', 'equipment_list_user', 'equipment_list_buyer', 'equipment_record_manuals', 'odp:view', 'odp:write', 'equipment_record:service'])]
    #[Legacy\Column(column: 'date_shipped', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    private ?\DateTimeInterface $dateShipped = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['equipment_record_detail', 'equipment_record:write', 'odp:view', 'odp:write', 'equipment_record:service'])]
    #[Legacy\Column(column: 'dgt_rev', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    private ?\DateTimeInterface $estimatedGreenTagDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['odp:view'])]
    #[Legacy\Column(column: 'first_estimated_green_tag_date', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    private ?\DateTimeInterface $firstEstimatedGreenTagDate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['equipment_record_detail', 'equipment_record:write', 'odp:view', 'odp:write'])]
    #[Legacy\Column(column: 'odp_note', transformer: Utf8ToHtmlEntities::class)]
    private ?string $odpComment = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['equipment_record_detail'])]
    private ?string $factoryComment = null;

    #[ORM\Column(name: 'date_commissioned', type: 'datetime', nullable: true)]
    #[Groups(['equipment_record', 'equipment_record_detail', 'equipment_list_user', 'equipment_list_buyer', 'equipment_record:service', 'equipment_record:collection'])]
    #[Legacy\Column(column: 'dt_commissioned', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    private ?\DateTimeInterface $dateCommissioned = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[Groups(['equipment_record', 'equipment_record_detail', 'equipment_record_manuals', 'odp:view', 'equipment_record:service', GraphQLMutationBuilder::LINK_NORMALIZATION_GROUP])]
    #[LinkField(fields: ['organization'], transformer: ArrayToLinkResource::class, options: ['property' => 'name', 'class' => Location::class])]
    #[ValidLocation(factory: true)]
    private ?Location $manufacturerLocation = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[Groups(['equipment_record', 'equipment_record_detail', 'odp:view', 'equipment_record:service', GraphQLMutationBuilder::LINK_NORMALIZATION_GROUP])]
    private ?Location $salesOrganisation = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[Groups(['equipment_record', 'equipment_record_detail', 'odp:view', 'equipment_record:service'])]
    #[Legacy\Column(column: 'sso_service', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    private ?Location $salesOrganisationService = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Order')]
    private ?Order $order = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['equipment_record_detail'])]
    private ?int $length = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['equipment_record_detail'])]
    private ?int $width = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['equipment_record_detail'])]
    private ?int $height = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['equipment_record_detail'])]
    private ?int $weight = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    #[Assert\IsTrue(groups: [Manual::PUBLISHABLE_VALIDATION_GROUP])]
    #[Groups(['publishable', 'equipment_record_detail', 'equipment_record_manuals'])]
    #[Legacy\Column(column: 'publishable', transformer: BooleanToChar::class, options: ['trueValue' => 'Y', 'falseValue' => 'N'])]
    private bool $publishable = false;

    /**
     * @var Collection<EquipmentSerial>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Support\EquipmentSerial', mappedBy: 'equipmentRecord', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['equipment_record_detail', 'equipment_record:admin'])]
    #[Assert\Valid]
    private Collection $serials;

    /**
     * @var Collection<Manual>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Support\Manual', mappedBy: 'equipmentRecord', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'DESC'])]
    #[Groups(['equipment_record_detail', 'equipment_record:admin', 'manual_public'])]
    private Collection $manuals;

    #[ORM\Column(name: 'green_tag_date', type: 'datetime', nullable: true)]
    #[Groups(['equipment_record_detail', 'equipment_record', 'equipment_record_manuals', 'odp:view', 'odp:write', 'equipment_record:service'])]
    #[Legacy\Column(column: 'dgt_act', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    private ?\DateTimeInterface $greenTagDate = null;

    #[ORM\Column(name: 'last_cbom_update_date', type: 'datetime', nullable: true)]
    #[Legacy\Column(column: 'last_cbom_update_date', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    private ?\DateTimeInterface $lastCBOMUpdateDate = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Groups(['equipment_record_detail'])]
    private ?string $projectNumber = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['equipment_record_detail'])]
    private ?string $optionsDescription = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[Groups(['odp:view'])]
    #[Legacy\Column(column: 'del_ctry', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    private ?Country $deliveredCountry = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['odp:view'])]
    private ?string $workOrder = null;

    #[ORM\Column(name: 'first_green_tag_date', type: 'datetime', nullable: true)]
    #[Legacy\Column(column: 'dgt_com', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    #[Groups(['odp:view', 'odp:write'])]
    private ?\DateTimeInterface $firstGreenTagDate = null;

    #[ORM\Column(name: 'yellow_tag_date', type: 'datetime', nullable: true)]
    #[Legacy\Column(column: 'dyt', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    #[Groups(['odp:view', 'odp:write', 'equipment_record'])]
    private ?\DateTimeInterface $yellowTagDate = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['odp:view'])]
    private bool $light = false;

    #[ORM\ManyToOne(targetEntity: EmissionRating::class)]
    #[LinkField(fields: ['energySource'], transformer: ArrayToLinkResource::class, options: ['property' => 'name'])]
    #[Groups(['equipment_record_detail', 'odp:view', 'equipment_record:service', GraphQLMutationBuilder::LINK_NORMALIZATION_GROUP])]
    private ?EmissionRating $emissionRating = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['green_tag_report'])]
    #[Assert\Choice(choices: [self::COMBINATION_ER_COMBINED, self::COMBINATION_PRE_ASSEMBLY])]
    private ?string $combinationMode = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['equipment_record:fms_contract'])]
    private ?string $contractFMS = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['equipment_record:fms_contract'])]
    private bool $tldLink = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['equipment_record:fms_contract'])]
    private bool $simCardStatusActive = false;

    #[ORM\Column(name: 'fms_end_use_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $fmsEndUseDate = null;

    #[ORM\Column(type: 'integer')]
    private int $fmsContractLength = 0;

    #[Groups(['equipment_record:fms_contract'])]
    private string $calculatedFmsEndUseDate = 'Not relevant, contract length is 0';

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Legacy\Column(column: 'hours')]
    #[Groups(['equipment_record', 'equipment_record_detail', 'equipment_record:service'])]
    private ?int $hourMeter = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['equipment_record_detail'])]
    #[Legacy\Column(column: 'date_warranty_end', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    private ?\DateTimeInterface $warrantyEndDate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['equipment_record_detail'])]
    #[Legacy\Column(column: 'warranty_conditions')]
    private ?string $warrantyConditions = null;

    /** @var Collection<PreDeliveryInspection> */
    #[ORM\OneToMany(targetEntity: PreDeliveryInspection::class, mappedBy: 'equipmentRecord')]
    private Collection $preDeliveryInspections;

    #[ORM\Column(type: 'string', length: 50, options: ['default' => 'ACTIVE'])]
    #[Groups(['equipment_record_detail'])]
    #[Assert\Choice(callback: [EquipmentRecordState::class, 'name'])]
    private string $state = EquipmentRecordState::ACTIVE->name;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['equipment_record_detail'])]
    #[Assert\Choice(callback: [EquipmentRecordStatus::class, 'values'])]
    private ?string $status = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $actualDeliveryDate = null;

    public function __construct()
    {
        $this->contracts = new ArrayCollection();
        $this->childrenEquipments = new ArrayCollection();
        $this->manuals = new ArrayCollection();
        $this->serials = new ArrayCollection();
        $this->equipmentShippingRecordLines = new ArrayCollection();
        $this->preDeliveryInspections = new ArrayCollection();
    }

    public function __toString(): string
    {
        return \sprintf('id: %d, legacyId: %d, sn: %s', $this->id, $this->legacyId, $this->serialNumber);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getParentEquipment(): ?self
    {
        return $this->parentEquipment;
    }

    public function setParentEquipment(?self $parentEquipment): self
    {
        $this->parentEquipment = $parentEquipment;

        return $this;
    }

    public function getEquipmentShippingRecordLines(): Collection
    {
        return $this->equipmentShippingRecordLines;
    }

    public function getChildrenEquipments(): Collection
    {
        return $this->childrenEquipments;
    }

    public function addChildrenEquipment(self $childrenEquipment): self
    {
        $this->childrenEquipments->add($childrenEquipment);
        $childrenEquipment->setParentEquipment($this);

        return $this;
    }

    public function removeChildrenEquipment(self $childrenCustomer): self
    {
        $this->childrenEquipments->removeElement($childrenCustomer);
        $childrenCustomer->setParentEquipment(null);

        return $this;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

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

    public function getBuyer(): ?Customer
    {
        return $this->buyer;
    }

    public function setBuyer(?Customer $buyer): self
    {
        $this->buyer = $buyer;

        return $this;
    }

    public function getCustomerSerialNumber(): ?string
    {
        return $this->customerSerialNumber;
    }

    public function setCustomerSerialNumber(?string $customerSerialNumber): self
    {
        $this->customerSerialNumber = $customerSerialNumber;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

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

    public function getAirport(): ?Airport
    {
        return $this->airport;
    }

    /**
     * If the airport is changed, the deliveredCountry is automatically updated
     * to match the country of the new airport.
     * This ensures that the deliveredCountry stays in sync with the airport
     * when applicable.
     */
    public function setAirport(?Airport $airport): self
    {
        $this->airport = $airport;

        if ($airport) {
            $this->setDeliveredCountry($airport->getCountry());
        }

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getContracts(): Collection
    {
        return $this->contracts;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getDateShipped(): ?\DateTimeInterface
    {
        return $this->dateShipped;
    }

    public function setDateShipped(?\DateTimeInterface $dateShipped): self
    {
        $this->dateShipped = $dateShipped;

        return $this;
    }

    public function getEstimatedGreenTagDate(): ?\DateTimeInterface
    {
        return $this->estimatedGreenTagDate;
    }

    public function setEstimatedGreenTagDate(?\DateTimeInterface $estimatedGreenTagDate): self
    {
        if (null === $this->firstEstimatedGreenTagDate && null !== $estimatedGreenTagDate) {
            $this->firstEstimatedGreenTagDate = $estimatedGreenTagDate;
        }

        $this->estimatedGreenTagDate = $estimatedGreenTagDate;

        return $this;
    }

    public function getFirstEstimatedGreenTagDate(): ?\DateTimeInterface
    {
        return $this->firstEstimatedGreenTagDate;
    }

    public function setFirstEstimatedGreenTagDate(?\DateTimeInterface $firstEstimatedGreenTagDate): self
    {
        $this->firstEstimatedGreenTagDate = $firstEstimatedGreenTagDate;

        return $this;
    }

    public function getOdpComment(): ?string
    {
        return $this->odpComment;
    }

    public function setOdpComment($odpComment): self
    {
        $this->odpComment = $odpComment;

        return $this;
    }

    public function getFactoryComment(): ?string
    {
        return $this->factoryComment;
    }

    public function setFactoryComment($factoryComment): self
    {
        $this->factoryComment = $factoryComment;

        return $this;
    }

    public function getDateCommissioned(): ?\DateTimeInterface
    {
        return $this->dateCommissioned;
    }

    public function setDateCommissioned(?\DateTimeInterface $dateCommissioned): self
    {
        $this->dateCommissioned = $dateCommissioned;

        return $this;
    }

    public function getManufacturerLocation(): ?Location
    {
        return $this->manufacturerLocation;
    }

    public function setManufacturerLocation(?Location $manufacturerLocation): self
    {
        $this->manufacturerLocation = $manufacturerLocation;

        return $this;
    }

    public function getSalesOrganisation(): ?Location
    {
        return $this->salesOrganisation;
    }

    public function setSalesOrganisation(?Location $salesOrganisation): self
    {
        $this->salesOrganisation = $salesOrganisation;

        return $this;
    }

    public function getSalesOrganisationService(): ?Location
    {
        return $this->salesOrganisationService;
    }

    public function setSalesOrganisationService(?Location $salesOrganisationService): self
    {
        $this->salesOrganisationService = $salesOrganisationService;

        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getMaintainer(): ?Customer
    {
        return $this->maintainer;
    }

    public function setMaintainer(?Customer $maintainer): self
    {
        $this->maintainer = $maintainer;

        return $this;
    }

    public function getSerials(): Collection
    {
        return $this->serials;
    }

    public function addSerial(EquipmentSerial $serial): self
    {
        if (!$this->serials->contains($serial)) {
            $serial->equipmentRecord = $this;
            $this->serials->add($serial);
        }

        return $this;
    }

    public function removeSerial(EquipmentSerial $serial): self
    {
        if ($this->serials->contains($serial)) {
            $this->serials->removeElement($serial);
        }

        return $this;
    }

    public function getManuals(): Collection
    {
        return $this->manuals;
    }

    public function addManual(Manual $manual): self
    {
        if (!$this->manuals->contains($manual)) {
            $manual->equipmentRecord = $this;
            $this->manuals->add($manual);
        }

        return $this;
    }

    public function removeManual(Manual $manual): self
    {
        if ($this->manuals->contains($manual)) {
            $this->manuals->removeElement($manual);
        }

        return $this;
    }

    public function getGreenTagDate(): ?\DateTimeInterface
    {
        return $this->greenTagDate;
    }

    public function setGreenTagDate(?\DateTimeInterface $greenTagDate): self
    {
        $this->greenTagDate = $greenTagDate;

        return $this;
    }

    public function getLastCBOMUpdateDate(): ?\DateTimeInterface
    {
        return $this->lastCBOMUpdateDate;
    }

    public function setLastCBOMUpdateDate(?\DateTimeInterface $lastCBOMUpdateDate): self
    {
        $this->lastCBOMUpdateDate = $lastCBOMUpdateDate;

        return $this;
    }

    public function getProjectNumber(): ?string
    {
        return $this->projectNumber;
    }

    public function setProjectNumber(?string $projectNumber): self
    {
        $this->projectNumber = $projectNumber;

        return $this;
    }

    public function isPublishable(): bool
    {
        return $this->publishable;
    }

    public function setPublishable(bool $publishable): self
    {
        $this->publishable = $publishable;

        return $this;
    }

    public function getOptionsDescription(): ?string
    {
        return $this->optionsDescription;
    }

    public function setOptionsDescription(?string $optionsDescription): self
    {
        $this->optionsDescription = $optionsDescription;

        return $this;
    }

    public function getDeliveredCountry(): ?Country
    {
        return $this->deliveredCountry;
    }

    public function setDeliveredCountry(?Country $deliveredCountry): self
    {
        $this->deliveredCountry = $deliveredCountry;

        return $this;
    }

    public function getWorkOrder(): ?string
    {
        return $this->workOrder;
    }

    public function setWorkOrder(?string $workOrder): self
    {
        $this->workOrder = $workOrder;

        return $this;
    }

    public function getFirstGreenTagDate(): ?\DateTimeInterface
    {
        return $this->firstGreenTagDate;
    }

    public function setFirstGreenTagDate(?\DateTimeInterface $firstGreenTagDate): self
    {
        $this->firstGreenTagDate = $firstGreenTagDate;

        return $this;
    }

    public function getYellowTagDate(): ?\DateTimeInterface
    {
        return $this->yellowTagDate;
    }

    public function setYellowTagDate(?\DateTimeInterface $yellowTagDate): self
    {
        $this->yellowTagDate = $yellowTagDate;

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

    public function getEmissionRating(): ?EmissionRating
    {
        return $this->emissionRating;
    }

    public function setEmissionRating(?EmissionRating $emissionRating): self
    {
        $this->emissionRating = $emissionRating;

        return $this;
    }

    public function getCombinationMode(): ?string
    {
        return $this->combinationMode;
    }

    public function setCombinationMode(?string $combinationMode): self
    {
        $this->combinationMode = $combinationMode;

        return $this;
    }

    public function getContractFMS(): ?string
    {
        return $this->contractFMS;
    }

    public function setContractFMS(?string $contractFMS): self
    {
        $this->contractFMS = $contractFMS;

        return $this;
    }

    public function isTldLink(): bool
    {
        return $this->tldLink;
    }

    public function setIsTldLink(bool $isTldLink): self
    {
        $this->tldLink = $isTldLink;

        return $this;
    }

    public function isSimCardStatusActive(): bool
    {
        return $this->simCardStatusActive;
    }

    public function setSimCardStatusActive(bool $simCardStatus): self
    {
        $this->simCardStatusActive = $simCardStatus;

        return $this;
    }

    public function getFmsEndUseDate(): ?\DateTimeInterface
    {
        return $this->fmsEndUseDate;
    }

    public function setFmsEndUseDate(?\DateTimeInterface $fmsEndUseDate): self
    {
        $this->fmsEndUseDate = $fmsEndUseDate;

        return $this;
    }

    public function getFmsContractLength(): int
    {
        return $this->fmsContractLength;
    }

    public function setFmsContractLength(int $fmsContractLength): self
    {
        $this->fmsContractLength = $fmsContractLength;

        return $this;
    }

    public function getCalculatedFmsEndUseDate(): string
    {
        if (null !== $this->getFmsEndUseDate()) {
            return $this->getFmsEndUseDate()->format('Y-m-d');
        }
        if ($this->getFmsContractLength() > 0 && null !== $this->getGreenTagDate()) {
            return (new \DateTime($this->getGreenTagDate()->format('Y-m-d H:i:s')))->add(new \DateInterval(\sprintf('P%sM', $this->getFmsContractLength())))->format('Y-m-d');
        }
        if ($this->getFmsContractLength() > 0) {
            return 'Undefined now as unit is not GT';
        }

        return $this->calculatedFmsEndUseDate;
    }

    #[Groups(['equipment_record_notification'])]
    public function getLastEquipmentShippingRecordsLine(): ?EquipmentShippingRecordLine
    {
        if (false === $this->getEquipmentShippingRecordLines()->last()) {
            return null;
        }

        return $this->getEquipmentShippingRecordLines()->last();
    }

    public function getHourMeter(): ?int
    {
        return $this->hourMeter;
    }

    public function setHourMeter(?int $hourMeter): self
    {
        $this->hourMeter = $hourMeter;

        return $this;
    }

    public function getWarrantyEndDate(): ?\DateTimeInterface
    {
        return $this->warrantyEndDate;
    }

    public function setWarrantyEndDate(?\DateTimeInterface $warrantyEndDate): self
    {
        $this->warrantyEndDate = $warrantyEndDate;

        return $this;
    }

    public function getWarrantyConditions(): ?string
    {
        return $this->warrantyConditions;
    }

    public function setWarrantyConditions(?string $warrantyConditions): self
    {
        $this->warrantyConditions = $warrantyConditions;

        return $this;
    }

    #[Groups(['equipment_record_detail'])]
    public function getWarrantyStatus(): string
    {
        if (null === $this->dateShipped) {
            return self::WARRANTY_STATUS_NOT_SHIPPED;
        }

        if (null === $this->warrantyEndDate || $this->warrantyEndDate <= new \DateTime()) {
            return self::WARRANTY_STATUS_EXPIRED;
        }

        $hours = $this->hourMeter ?? 0;
        $warrantyConditions = $this->warrantyConditions ?? '';

        if ($hours < 2000 && '' === $warrantyConditions) {
            return self::WARRANTY_STATUS_EFFECTIVE;
        }

        if (($hours >= 2000 && $hours <= 3000) || ($hours < 2000 && '' !== $warrantyConditions)) {
            return self::WARRANTY_STATUS_MAYBE_EXPIRED;
        }

        return self::WARRANTY_STATUS_EXPIRED;
    }

    /** @return Collection<PreDeliveryInspection> */
    public function getPreDeliveryInspections(): Collection
    {
        return $this->preDeliveryInspections;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getActualDeliveryDate(): ?\DateTimeInterface
    {
        return $this->actualDeliveryDate;
    }

    public function setActualDeliveryDate(?\DateTimeInterface $actualDeliveryDate): self
    {
        $this->actualDeliveryDate = $actualDeliveryDate;

        return $this;
    }

    #[Groups(['odp:view'])]
    public function getLastPreDeliveryInspection(): ?PreDeliveryInspection
    {
        if ($this->preDeliveryInspections->isEmpty()) {
            return null;
        }

        return array_reduce($this->preDeliveryInspections->toArray(),
            static function (?PreDeliveryInspection $latest, PreDeliveryInspection $current) {
                if (null === $latest || $current->getCreatedAt() > $latest->getCreatedAt()) {
                    return $current;
                }

                return $latest;
            }
        );
    }

    public function addPreDeliveryInspection(PreDeliveryInspection $preDeliveryInspection): self
    {
        if (!$this->preDeliveryInspections->contains($preDeliveryInspection)) {
            $this->preDeliveryInspections->add($preDeliveryInspection);
            $preDeliveryInspection->setEquipmentRecord($this);
        }

        return $this;
    }

    public function removePreDeliveryInspection(PreDeliveryInspection $preDeliveryInspection): self
    {
        if ($this->preDeliveryInspections->removeElement($preDeliveryInspection)) {
            if ($preDeliveryInspection->getEquipmentRecord() === $this) {
                $preDeliveryInspection->setEquipmentRecord(null);
            }
        }

        return $this;
    }

    #[Groups(['equipment_record_esr_check'])]
    public function isAvailableForEquipmentShippingRecord(): bool
    {
        foreach ($this->getEquipmentShippingRecordLines() as $line) {
            $equipmentShippingRecord = $line->equipmentShippingRecord;

            if (null !== $equipmentShippingRecord && EquipmentShippingRecord::CLOSED !== $equipmentShippingRecord->getStatus()) {
                return false;
            }
        }

        return true;
    }
}
