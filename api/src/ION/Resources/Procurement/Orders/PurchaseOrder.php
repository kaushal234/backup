<?php

declare(strict_types=1);

namespace App\ION\Resources\Procurement\Orders;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use App\Controller\File\ZipController;
use App\Controller\PdfController;
use App\Entity\Activity\Comment;
use App\Entity\PdfFactoryInterface;
use App\Formatter\Snappy\AdapterFactory\PurchaseOrderLabelsAdapterFactory;
use App\ION\DataProcessor\IONDataProcessor;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Dto\Procurement\Orders\PurchaseOrderPdfInput;
use App\ION\Filter\DataAreaFilter;
use App\ION\Filter\Procurement\Orders\PurchaseOrderDateFilter;
use App\ION\Filter\Procurement\Orders\PurchaseOrderOpenFilter;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerGetterInterface;
use App\ION\Resources\MasterData\EnterpriseModel\Employee;
use App\ION\Resources\SiteInterface;
use App\Serializer\Normalizer\ActivityNormalizer;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => PurchaseOrder::COLLECTION_NORMALIZATION_GROUPS],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
            provider: CachedIONCollectionDataProvider::class,
        ),
        new Put(
            security: "is_granted('ACCESS_PEOPLE') or is_granted('BUSINESS_PARTNER_VOTER', object)",
            provider: CachedIONItemDataProvider::class,
            processor: IONDataProcessor::class,
        ),
        new Put(
            uriTemplate: '/purchase_orders/{orderIdentifier}/pdf_labels',
            formats: ['pdf' => 'application/pdf', 'json'],
            defaults: ['purpose' => PurchaseOrderLabelsAdapterFactory::PURPOSE],
            controller: PdfController::class,
            denormalizationContext: ['groups' => ['purchase_order:pdf_input']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
            input: PurchaseOrderPdfInput::class,
            read: false,
            name: 'pdf_labels',
        ),
        new Get(
            normalizationContext: ['groups' => PurchaseOrder::ITEM_NORMALIZATION_GROUPS, ActivityNormalizer::NORMALIZE_ACTIVITY_ATTRIBUTE => 'comment'],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('BUSINESS_PARTNER_VOTER', object)",
            provider: CachedIONItemDataProvider::class,
        ),
        new Get(
            uriTemplate: '/purchase_orders/{orderIdentifier}/zip',
            formats: ['zip' => 'application/zip'],
            controller: ZipController::class,
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
            name: 'ion_purchase_order_zip',
            provider: CachedIONItemDataProvider::class,
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => PurchaseOrder::ITEM_NORMALIZATION_GROUPS],
    denormalizationContext: ['groups' => ['purchase_order:write']],
    extraProperties: [
        Comment::VENDOR_USER_COMMENTABLE => true,
    ]
)]
#[ApiFilter(PurchaseOrderDateFilter::class)]
#[ApiFilter(PurchaseOrderOpenFilter::class)]
#[ApiFilter(DataAreaFilter::class, properties: ['buyFromSupplierCode', 'otherLanguage', 'onlyOpenedLines'])]
class PurchaseOrder implements BusinessPartnerGetterInterface, SiteInterface, PdfFactoryInterface
{
    final public const DATAAREA_FILTERS = ['scenario' => 'ACT', 'itemCodeSystem' => 'SUP'];

    final public const COLLECTION_NORMALIZATION_GROUPS = ['purchase_order', 'quantity', 'amount', 'address', 'employee', 'ion:text'];
    final public const ITEM_NORMALIZATION_GROUPS = ['purchase_order', 'quantity', 'amount', 'address', 'employee', 'purchase_order:details', 'ion:text'];

    /** @var string[] */
    final public const OPEN_STATUSES = [self::STATUS_SENT, self::STATUS_IN_PROCESS, self::STATUS_MODIFIED];

    /**
     * @var string
     */
    final public const LATE = 'Late';

    /**
     * @var string
     */
    final public const UNCONFIRMED = 'Unconfirmed';

    /**
     * @var string
     */
    final public const CONFIRMED = 'Confirmed';

    /**
     * @var string
     */
    final public const TO_BE_DELIVERED_7_DAYS = 'To be delivered within 7 days';

    private const STATUS_SENT = '15';
    private const STATUS_IN_PROCESS = '20';
    private const STATUS_MODIFIED = '35';

    #[ApiProperty(identifier: true)]
    #[Groups(['purchase_order', IONDataProcessor::ION_SYNC])]
    public string $orderIdentifier;

    #[Groups(['purchase_order'])]
    public string $purchaseOfficeCode;

    #[Groups(['purchase_order'])]
    public string $orderTypeCode;

    #[Groups(['purchase_order'])]
    public string $buyFromSupplierCode;

    #[Groups(['purchase_order'])]
    public ?Employee $buyer;

    #[Groups(['purchase_order'])]
    public \DateTimeInterface $orderDatetime;

    #[Groups(['purchase_order'])]
    public \DateTimeInterface $plannedReceiptDate;

    #[Groups(['purchase_order'])]
    public string $reference1;

    #[Groups(['purchase_order'])]
    public string $reference2;

    #[Groups(['purchase_order'])]
    public string $orderStatus;

    #[Groups(['purchase_order:write'])]
    public ?string $editMessage = null;

    /**
     * @var array<PurchaseOrderLine>
     */
    #[Assert\Valid]
    #[Assert\Count(min: 1)]
    #[Groups(['purchase_order', 'purchase_order:write', IONDataProcessor::ION_SYNC])]
    private array $lines = [];

    public function getLines(): array
    {
        return $this->lines;
    }

    public function addLine(PurchaseOrderLine $purchaseOrderLine): self
    {
        $this->lines[] = $purchaseOrderLine;

        return $this;
    }

    public function removeLine(PurchaseOrderLine $purchaseOrderLine): self
    {
        foreach ($this->lines as $index => $line) {
            if ($line->lineIdentifier === $purchaseOrderLine->lineIdentifier) {
                unset($this->lines[$index]);
                $this->lines = array_values($this->lines);

                return $this;
            }
        }

        return $this;
    }

    public function getLineByKey(string $lineIdentifier, int $sequence): ?PurchaseOrderLine
    {
        foreach ($this->getLines() as $line) {
            if ($line->lineIdentifier === $lineIdentifier && $line->sequence === $sequence) {
                return $line;
            }
        }

        return null;
    }

    #[Groups(['purchase_order'])]
    public function getStatus(): array
    {
        $status = [];

        foreach ($this->getLines() as $orderLine) {
            if ($orderLine->isLate()) {
                $status[self::LATE] = true;
            }

            if ($orderLine->isUnconfirmed()) {
                $status[self::UNCONFIRMED] = true;
            }

            if ($orderLine->isToBeDeliveredWithin7Days()) {
                $status[self::TO_BE_DELIVERED_7_DAYS] = true;
            }
        }

        return array_keys($status);
    }

    public function isUnconfirmed(): bool
    {
        return \in_array(self::UNCONFIRMED, $this->getStatus(), true);
    }

    public function getBusinessPartnerCode(): string
    {
        return $this->buyFromSupplierCode;
    }

    public function getSiteNumber(): ?int
    {
        return (int) mb_substr($this->purchaseOfficeCode, 0, 3);
    }

    public function getFileName(): string
    {
        return \sprintf('tld_purchase_order_labels_%s_erp_%s.pdf', $this->orderIdentifier, $this->getSiteNumber());
    }
}
