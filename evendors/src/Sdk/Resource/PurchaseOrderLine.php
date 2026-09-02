<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-import-type TextItemStructure from TextItem
 *
 * @psalm-import-type TextItemStructure from TextItem
 *
 * @phpstan-type PurchaseOrderLineStructure array{"@id": non-empty-string, "@type": non-empty-string, "id": ?positive-int, "position": positive-int|string, "sequence": int, "partNumber": non-empty-string, "erp": ?positive-int, "supplierNumber": ?non-empty-string, "quantity": positive-int, "late": bool, "unconfirmed": bool, "toBeDeliveredWithin7Days": bool, "deliveredQuantity": int, "backQuantity": int, "plannedDeliveryDate": ?non-empty-string, "confirmedSupplierDate": ?non-empty-string, "rescheduledDeliveryDate": ?non-empty-string, "shippingDate": ?non-empty-string, "price": ?Amount, "revision": ?int, "itemDescription": string, "signalCode": ?non-empty-string, "leadTime": ?non-empty-string, "blocked": bool, "isConfirmable": bool, "isToCancel": bool, "canceled": ?bool, "lineState": string, "lineTexts": null|list<TextItemStructure> }
 *
 * @psalm-type PurchaseOrderLineStructure = array{"@id": non-empty-string, "@type": non-empty-string, "id": ?positive-int, "position": positive-int|string, "sequence": int, "partNumber": non-empty-string, "erp": ?positive-int, "supplierNumber": ?non-empty-string, "quantity": positive-int, "late": bool, "unconfirmed": bool, "toBeDeliveredWithin7Days": bool, "deliveredQuantity": int, "backQuantity": int, "plannedDeliveryDate": ?non-empty-string, "confirmedSupplierDate": ?non-empty-string, "rescheduledDeliveryDate": ?non-empty-string, "shippingDate": ?non-empty-string, "price": ?Amount, "revision": ?int, "itemDescription": string, "signalCode": ?non-empty-string, "leadTime": ?non-empty-string, "blocked": bool, "isConfirmable": bool, "isToCancel": bool, "canceled": ?bool, "lineState": string, "lineTexts": null|list<TextItemStructure> }
 */
final class PurchaseOrderLine implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public int $quantityLabel = 0;
    public int $labelDeliveredQuantity = 0;
    public string $packingSlip;

    /**
     * @param list<TextItem> $lineTexts
     */
    public function __construct(
        public readonly string $iri,
        public readonly ?int $id,
        public readonly int|string $lineIdentifier,
        public readonly int $sequence,
        public readonly string $purchaseOrderIdentifier,
        public readonly string $partNumber,
        public readonly ?string $supplierNumber,
        public readonly ?int $erp,
        public readonly float|int $quantity,
        public readonly float|int $deliveredQuantity,
        public readonly string $plannedDeliveryDate,
        public ?string $confirmedSupplierDate,
        public readonly ?string $rescheduledDeliveryDate,
        public readonly ?string $shippingDate,
        public readonly ?string $signalCode,
        public readonly ?string $leadTime,
        public readonly ?Amount $price,
        public readonly int|string|null $revision,
        public readonly string $description,
        public readonly ?bool $blocked,
        public readonly bool $late,
        public readonly bool $unconfirmed,
        public readonly bool $toBeDeliveredIn7Days,
        public readonly bool $isConfirmable,
        public readonly bool $isToCancel,
        public readonly bool $canceled,
        public readonly string $lineState,
        public readonly ?bool $expired = false,
        public readonly ?int $site = null,
        public readonly ?string $supplierItemCode = null,
        public readonly ?string $project = null,
        public readonly ?string $revisionEffectiveDate = null,
        public readonly ?string $revisionExpiryDate = null,
        public readonly ?array $lineTexts = [],
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public function getToBeDeliveredQuantity(): int
    {
        return $this->quantity - $this->deliveredQuantity;
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('PurchaseOrderLine'),
            'sequence' => Type\int(),
            'lineIdentifier' => Type\non_empty_string(),
            'itemCode' => Type\non_empty_string(),
            'supplierItemCode' => Type\nullable(Type\non_empty_string()),
            'engineeringItemRevision' => Type\nullable(Type\string()),
            'plannedReceiptDate' => Type\nullable(Type\non_empty_string()),
            'confirmedSupplierDate' => Type\nullable(Type\non_empty_string()),
            'quantity' => Quantity::getTypeStructure(),
            'price' => Type\optional(Amount::getTypeStructure()),
            'late' => Type\bool(),
            'unconfirmed' => Type\bool(),
            'toBeDeliveredWithin7Days' => Type\bool(),
            'backOrderQuantity' => Quantity::getTypeStructure(),
            'site' => Type\nullable(Type\int()),
            'project' => Type\nullable(Type\string()),
            'engineeringRevisionEffectiveDate' => Type\nullable(Type\non_empty_string()),
            'engineeringRevisionExpiryDate' => Type\nullable(Type\non_empty_string()),
            'expired' => Type\bool(),
            'isToCancel' => Type\bool(),
            'canceled' => Type\bool(),
            'isConfirmable' => Type\bool(),
        ], allow_unknown_fields: true);
    }
}
