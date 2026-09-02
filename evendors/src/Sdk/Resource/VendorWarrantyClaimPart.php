<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * @phpstan-import-type PersonStructure from Person
 *
 * @psalm-import-type PersonStructure from Person
 *
 * @phpstan-type VendorWarrantyClaimPartStructure array{"@id": non-empty-string, "@type": "VendorWarrantyClaimPart", "serialNumber": null|string, "vendorPartNumber": ?string, "vendorSerialNumber": ?string, "failureType": ?string, "failureSystem": ?string, "ship": bool, "receivedQuantity": ?int, "createdAt": string, "createdBy": PersonStructure, "partNumber": string, "description": string, "quantity": float, "unitOfMeasure": string, "deletedAt": ?string, "deletedBy": ?PersonStructure, "id": int}
 *
 * @psalm-type VendorWarrantyClaimPartStructure = array{"@id": non-empty-string, "@type": "VendorWarrantyClaimPart", "serialNumber": null|string, "vendorPartNumber": ?string, "vendorSerialNumber": ?string, "failureType": ?string, "failureSystem": ?string, "ship": bool, "receivedQuantity": ?int, "createdAt": string, "createdBy": PersonStructure, "partNumber": string, "description": string, "quantity": float, "unitOfMeasure": string, "deletedAt": ?string, "deletedBy": ?PersonStructure, "id": int}
 */
final class VendorWarrantyClaimPart implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly ?string $serialNumber,
        public readonly ?string $vendorPartNumber,
        public readonly ?string $vendorSerialNumber,
        public readonly ?string $failureType,
        public readonly ?string $failureSystem,
        public readonly bool $ship,
        public readonly ?int $receivedQuantity,
        public readonly string $createdAt,
        public readonly Person $createdBy,
        public readonly string $partNumber,
        public readonly string $description,
        public readonly float $quantity,
        public readonly string $unitOfMeasure,
        public readonly ?string $deletedAt,
        public readonly ?Person $deletedBy,
    ) {
    }

    /**
     * @return Type\TypeInterface<VendorWarrantyClaimPartStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('VendorWarrantyClaimPart'),
            'id' => Type\int(),
            'serialNumber' => Type\nullable(Type\string()),
            'vendorPartNumber' => Type\nullable(Type\string()),
            'vendorSerialNumber' => Type\nullable(Type\string()),
            'failureType' => Type\nullable(Type\string()),
            'failureSystem' => Type\nullable(Type\string()),
            'ship' => Type\bool(),
            'receivedQuantity' => Type\nullable(Type\int()),
            'createdAt' => Type\string(),
            'createdBy' => Person::getTypeStructure(),
            'partNumber' => Type\string(),
            'description' => Type\string(),
            'quantity' => Type\union(Type\int(), Type\float()),
            'unitOfMeasure' => Type\string(),
            'deletedAt' => Type\nullable(Type\string()),
            'deletedBy' => Type\nullable(Person::getTypeStructure()),
        ], allow_unknown_fields: true);
    }

    public function getIri(): string
    {
        return $this->iri;
    }
}
