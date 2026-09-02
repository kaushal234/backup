<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-type MaterialRequirementsPlanningStructure array{"@id": non-empty-string, "@typ": non-empty-string, erp: int, purchaseOrder: ?string, supplierNumber: ?string, warehouse: ?string, partNumber: string, supplierPartNumber: string, description: string, orderedQuantity: string, revision: string, plannedOrderDate: string, plannedDeliveryDate: string, vendorPartNumber: string, price: ?float, currency: ?string}
 *
 * @psalm-type MaterialRequirementsPlanningStructure = array{"@id": non-empty-string, "@typ": non-empty-string, erp: int, purchaseOrder: ?string, supplierNumber: ?string, warehouse: ?string, partNumber: string, supplierPartNumber: string, description: string, orderedQuantity: string, revision: string, plannedOrderDate: string, plannedDeliveryDate: string, vendorPartNumber: string, price: ?float, currency: ?string}
 */
final class MaterialRequirementsPlanning implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        private readonly string $iri,
        public readonly int $erp,
        public readonly ?string $purchaseOrder,
        public readonly string $supplierNumber,
        public readonly ?string $warehouse,
        public readonly string $partNumber,
        public readonly string $supplierPartNumber,
        public readonly string $description,
        public readonly string $orderedQuantity,
        public readonly string $revision,
        public readonly string $plannedOrderDate,
        public readonly string $plannedDeliveryDate,
        public readonly ?string $vendorPartNumber,
        public readonly int|float|null $price = null,
        public readonly ?string $currency = null,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('PlannedOrder'),
            'plannedOrderIdentifier' => Type\string(),
            'item' => Type\string(),
            'supplierPartNumber' => Type\string(),
            'status' => Type\string(),
            'plannedStartDate' => Type\string(),
            'plannedFinishDate' => Type\string(),
            'buyFromBusinessPartner' => Type\string(),
            'buyFromBusinessPartnerName' => Type\string(),
            'itemDescription' => Type\string(),
            'quantity' => Type\string(),
            'unitOfMeasure' => Type\string(),
            'price' => Type\optional(Type\nullable(Type\union(Type\int(), Type\float()))),
            'currency' => Type\optional(Type\nullable(Type\string())),
        ], allow_unknown_fields: true);
    }
}
