<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-import-type VendorWarrantyClaimTypeStructure from VendorWarrantyClaimType
 *
 * @psalm-import-type VendorWarrantyClaimTypeStructure from VendorWarrantyClaimType
 *
 * @phpstan-import-type VendorWarrantyClaimPartStructure from VendorWarrantyClaimPart
 *
 * @psalm-import-type VendorWarrantyClaimPartStructure from VendorWarrantyClaimPart
 *
 * @phpstan-import-type NonConformityStructure from NonConformity
 *
 * @psalm-import-type NonConformityStructure from NonConformity
 *
 * @phpstan-import-type VendorWarrantyClaimFileStructure from VendorWarrantyClaimFile
 *
 * @psalm-import-type VendorWarrantyClaimFileStructure from VendorWarrantyClaimFile
 *
 * @phpstan-type NCRVendorWarrantyClaimStructure array{"@id": non-empty-string, "@type": non-empty-string, nonConformity: NonConformityStructure, type: VendorWarrantyClaimTypeStructure, status: string|array{name: string}, id: int, "files": list<VendorWarrantyClaimFileStructure>}
 *
 * @psalm-type NCRVendorWarrantyClaimStructure = array{"@id": non-empty-string, "@type": non-empty-string, nonConformity: NonConformityStructure, type: VendorWarrantyClaimTypeStructure, status: string|array{name: string}, id: int, "files": list<VendorWarrantyClaimFileStructure>}
 */
final class NCRVendorWarrantyClaim implements VendorWarrantyClaimInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param list<VendorWarrantyClaimPart> $parts
     * @param list<Comment>                 $activities
     * @param list<VendorWarrantyClaimFile> $files
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly NonConformity $nonConformity,
        public readonly ?VendorWarrantyClaimType $type = null,
        public readonly ?VendorWarrantyClaimStatus $status = null,
        public readonly ?string $statusUpdatedAt = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $requestedSupplierAction = null,
        public readonly int|float|null $requestedCreditAmount = null,
        public readonly int|float|null $actualCreditAmount = null,
        public readonly ?string $supplierName = null,
        public readonly ?string $supplierNumber = null,
        public readonly ?int $supplierErp = null,
        public readonly array $parts = [],
        public readonly ?Person $poster = null,
        public readonly ?Person $assignee = null,
        public readonly ?Location $location = null,
        public readonly ?string $supplierReturnMerchandiseAuthorization = null,
        public readonly ?string $supplierShippingInstruction = null,
        public readonly ?string $supplierShipperName = null,
        public readonly ?string $trackingNumber = null,
        public readonly int|float|null $supplierCreditAmount = null,
        public readonly ?string $supplierCreditNote = null,
        public readonly ?bool $shipBackDefectivePart = null,
        public readonly ?bool $scarRequested = null,
        public readonly ?bool $accepted = null,
        public readonly ?SupplierCorrectiveActionRequest $supplierCorrectiveActionRequest = null,
        public readonly array $activities = [],
        public readonly array $files = [],
        public readonly ?string $vendorToRespondAt = null,
    ) {
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('NcrVendorWarrantyClaim'),
            'nonConformity' => NonConformity::getTypeStructure(),
            'type' => Type\optional(Type\nullable(VendorWarrantyClaimType::getTypeStructure())),
            'id' => Type\int(),
            'status' => Type\union(VendorWarrantyClaimStatus::getTypeStructure(), Type\shape([
                '@id' => Type\non_empty_string(),
                '@type' => Type\literal_scalar('VendorWarrantyClaimStatus'),
                'name' => VendorWarrantyClaimStatus::getTypeStructure(),
                'description' => Type\string(),
                'position' => Type\int(),
            ])),
            'statusUpdatedAt' => Type\optional(Type\nullable(Type\string())),
            'createdAt' => Type\optional(Type\string()),
            'requestedSupplierAction' => Type\optional(Type\string()),
            'requestedCreditAmount' => Type\optional(Type\nullable(Type\union(Type\int(), Type\float()))),
            'actualCreditAmount' => Type\optional(Type\nullable(Type\union(Type\int(), Type\float()))),
            'supplierName' => Type\optional(Type\nullable(Type\string())),
            'supplierNumber' => Type\optional(Type\nullable(Type\string())),
            'supplierErp' => Type\optional(Type\nullable(Type\int())),
            'parts' => Type\optional(Type\vec(VendorWarrantyClaimPart::getTypeStructure())),
            'poster' => Type\optional(Type\nullable(Person::getTypeStructure())),
            'assignee' => Type\optional(Type\nullable(Person::getTypeStructure())),
            'location' => Type\optional(Type\nullable(Location::getTypeStructure())),
            'supplierReturnMerchandiseAuthorization' => Type\optional(Type\nullable(Type\string())),
            'supplierShippingInstruction' => Type\optional(Type\nullable(Type\string())),
            'supplierShipperName' => Type\optional(Type\nullable(Type\string())),
            'trackingNumber' => Type\optional(Type\nullable(Type\string())),
            'supplierCreditAmount' => Type\optional(Type\nullable(Type\union(Type\int(), Type\float()))),
            'supplierCreditNote' => Type\optional(Type\nullable(Type\string())),
            'shipBackDefectivePart' => Type\optional(Type\nullable(Type\bool())),
            'scarRequested' => Type\optional(Type\nullable(Type\bool())),
            'accepted' => Type\optional(Type\nullable(Type\bool())),
            'activity' => Type\optional(Type\union(Type\vec(Comment::getTypeStructure()), Type\vec(Log::getLegacyTypeStructure()))),
            'files' => Type\optional(Type\vec(VendorWarrantyClaimFile::getTypeStructure())),
            'supplierCorrectiveActionRequest' => Type\optional(Type\nullable(SupplierCorrectiveActionRequest::getSimplifiedTypeStructure())),
            'vendorToRespondAt' => Type\optional(Type\nullable(Type\non_empty_string())),
        ], allow_unknown_fields: true);
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public function getModule(): string
    {
        return self::NCR_MODULE;
    }
}
