<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-import-type PurchaseOrderLineStructure from PurchaseOrderLine
 *
 * @psalm-import-type PurchaseOrderLineStructure from PurchaseOrderLine
 *
 * @phpstan-import-type CommentStructure from Comment
 *
 * @psalm-import-type CommentStructure from Comment
 *
 * @phpstan-type PurchaseOrderStructure array{"@id": non-empty-string, "@type": "PurchaseOrder", "id": positive-int, "erp": positive-int, "supplierNumber": ?non-empty-string, "referenceA": ?non-empty-string, "referenceB": ?non-empty-string, "orderDate": ?non-empty-string, "deliveryDate": ?non-empty-string, "confirmedDeliveryDate": ?non-empty-string, "orderLines": null|list<PurchaseOrderLineStructure>, "activity": null|list<CommentStructure>, "status": ?list<string>}
 *
 * @psalm-type PurchaseOrderStructure = array{"@id": non-empty-string, "@type": "PurchaseOrder", "id": positive-int, "erp": positive-int, "supplierNumber": ?non-empty-string, "referenceA": ?non-empty-string, "referenceB": ?non-empty-string, "orderDate": ?non-empty-string, "deliveryDate": ?non-empty-string, "confirmedDeliveryDate": ?non-empty-string, "orderLines": null|list<PurchaseOrderLineStructure>, "activity": null|list<CommentStructure>, "status": ?list<string>}
 */
final class PurchaseOrder implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public const LATE_CRITERIA = 'late';
    public const UNCONFIRMED_CRITERIA = 'unconfirmed';

    /**
     * @param list<PurchaseOrderLine> $lines
     * @param list<Comment>           $comments
     * @param list<string>            $status
     */
    public function __construct(
        public readonly string $iri,
        public readonly string|int $id,
        public readonly ?int $erp,
        public readonly ?string $supplierNumber,
        public readonly ?string $referenceA,
        public readonly ?string $referenceB,
        public readonly ?string $orderDate,
        public readonly ?string $deliveryDate,
        public readonly ?string $confirmedDeliveryDate,
        public readonly array $lines,
        public readonly array $comments,
        public readonly array $status = [],
        public int $orderLineOpen = 0,
    ) {
        $this->orderLineOpen = $this->countOpenOrderLine();
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('PurchaseOrder'),
            'orderIdentifier' => Type\string(),
            'purchaseOfficeCode' => Type\string(),
            'orderTypeCode' => Type\string(),
            'buyFromSupplierCode' => Type\optional(Type\nullable(Type\non_empty_string())),
            'orderDatetime' => Type\nullable(Type\non_empty_string()),
            'plannedReceiptDate' => Type\nullable(Type\non_empty_string()),
            'reference1' => Type\nullable(Type\string()),
            'reference2' => Type\nullable(Type\string()),
            'lines' => Type\optional(Type\vec(PurchaseOrderLine::getTypeStructure())),
            'activity' => Type\optional(Type\vec(Comment::getTypeStructure())),
            'status' => type\optional(Type\vec(Type\string())),
        ], allow_unknown_fields: true);
    }

    private function countOpenOrderLine(): int
    {
        $openOrderLine = 0;
        foreach ($this->lines as $line) {
            if ($line->isConfirmable) {
                ++$openOrderLine;
            }
        }

        return $openOrderLine;
    }
}
