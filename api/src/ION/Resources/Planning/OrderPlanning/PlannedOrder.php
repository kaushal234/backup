<?php

declare(strict_types=1);

namespace App\ION\Resources\Planning\OrderPlanning;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Serializer\Filter\GroupFilter;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerGetterInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']], provider: CachedIONCollectionDataProvider::class),
        new Get(controller: NotFoundAction::class, output: false, read: false, provider: CachedIONItemDataProvider::class),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['planned_order']],
    denormalizationContext: [],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
)]
#[ApiFilter(DataAreaFilter::class, properties: ['buyFromBusinessPartners'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => ['planned_order:monthly']])]
class PlannedOrder implements BusinessPartnerGetterInterface
{
    /** @var string[] */
    final public const DATAAREA_FILTERS = [
        'itemCodeSystem' => self::ITEM_CODE_SYSTEM_SUPPLIER_TYPE,
        'scenario' => self::SCENARIO_ACTUAL,
        'orderType' => self::TYPE_PLANNED_PURCHASE_ORDER,
    ];

    private const ITEM_CODE_SYSTEM_SUPPLIER_TYPE = 'SUP';
    private const SCENARIO_ACTUAL = 'ACT';
    private const TYPE_PLANNED_PURCHASE_ORDER = '5';

    #[ApiProperty(identifier: true)]
    #[Groups(['planned_order'])]
    public string $plannedOrderIdentifier;

    #[Groups(['planned_order'])]
    public string $item;

    #[Groups(['planned_order'])]
    public string $supplierPartNumber;

    #[Groups(['planned_order'])]
    public string $status;

    #[Groups(['planned_order'])]
    public ?\DateTimeInterface $plannedStartDate;

    #[Groups(['planned_order'])]
    public ?\DateTimeInterface $plannedFinishDate;

    #[Groups(['planned_order'])]
    public string $buyFromBusinessPartner;

    #[Groups(['planned_order'])]
    public string $buyFromBusinessPartnerName;

    #[Groups(['planned_order'])]
    public string $itemDescription;

    #[Groups(['planned_order'])]
    public string $quantity;

    #[Groups(['planned_order'])]
    public string $unitOfMeasure;

    #[Groups(['planned_order'])]
    public ?float $price = null;

    #[Groups(['planned_order'])]
    public ?string $currency = null;

    #[Groups(['planned_order'])]
    private string $buyFromSupplierCode;

    public function getBusinessPartnerCode(): string
    {
        return $this->buyFromSupplierCode;
    }
}
