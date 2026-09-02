<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\SimplifiedTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-import-type VendorWarrantyClaimPartStructure from VendorWarrantyClaimPart
 *
 * @psalm-import-type VendorWarrantyClaimPartStructure from VendorWarrantyClaimPart
 *
 * @phpstan-import-type PersonStructure from Person
 *
 * @psalm-import-type PersonStructure from Person
 *
 * @phpstan-import-type VendorWarrantyClaimFileStructure from VendorWarrantyClaimFile
 *
 * @psalm-import-type VendorWarrantyClaimFileStructure from VendorWarrantyClaimFile
 *
 * @phpstan-import-type LegacyFileStructure from LegacyFile
 *
 * @psalm-import-type LegacyFileStructure from LegacyFile
 *
 * @phpstan-import-type WarrantyClaimStructure from WarrantyClaim
 *
 * @psalm-import-type WarrantyClaimStructure from WarrantyClaim
 * @psalm-import-type VendorWarrantyClaimMainFileStructure from VendorWarrantyClaimMainFile
 *
 * @phpstan-type WCVendorWarrantyClaimStructure array{"@id": non-empty-string, "@type": "WcVendorWarrantyClaim", "warrantyClaimId": int, "statusUpdatedAt": ?string, "assignee": PersonStructure, "status": string|array{name: string}, "createdAt": string, "requestedSupplierAction": string, "requestedCreditAmount": int, "supplierCreditAmount": null|int|float, "actualCreditAmount": null|int|float, "supplierName": string, "supplierNumber": string, "supplierErp": int, "mainFile": null|VendorWarrantyClaimMainFileStructure, "parts": list<VendorWarrantyClaimPartStructure>, "id": int, "files": list<VendorWarrantyClaimFileStructure>, warrantyClaim: WarrantyClaimStructure, "warrantyClaimFiles": list<LegacyFileStructure>}
 *
 * @psalm-type WCVendorWarrantyClaimStructure = array{"@id": non-empty-string, "@type": "WcVendorWarrantyClaim", "warrantyClaimId": int, "statusUpdatedAt": ?string, "assignee": PersonStructure, "status": string|array{name: string}, "createdAt": string, "requestedSupplierAction": string, "requestedCreditAmount": int, "supplierCreditAmount": null|int|float, "actualCreditAmount": null|int|float, "supplierName": string, "supplierNumber": string, "supplierErp": int, "mainFile": null|VendorWarrantyClaimMainFileStructure, "parts": list<VendorWarrantyClaimPartStructure>, "id": int, "files": list<VendorWarrantyClaimFileStructure>, warrantyClaim: WarrantyClaimStructure, "warrantyClaimFiles": list<LegacyFileStructure>}
 *
 * @phpstan-type SimplifiedWCVendorWarrantyClaimStructure array{"@id": non-empty-string, "@type": "WcVendorWarrantyClaim", "warrantyClaimId": int, "statusUpdatedAt": ?string, "assignee": PersonStructure, "status": string|array{name: string}, "createdAt": string, "requestedSupplierAction": string, "requestedCreditAmount": int, "supplierCreditAmount": null|int|float, "actualCreditAmount": null|int|float, "supplierName": string, "supplierNumber": string, "supplierErp": int, "parts": list<VendorWarrantyClaimPartStructure>, "id": int, "files": list<VendorWarrantyClaimFileStructure>, "warrantyClaimFiles": list<LegacyFileStructure>}
 *
 * @psalm-type SimplifiedWCVendorWarrantyClaimStructure = array{"@id": non-empty-string, "@type": "WcVendorWarrantyClaim", "warrantyClaimId": int, "statusUpdatedAt": ?string, "assignee": PersonStructure, "status": string|array{name: string}, "createdAt": string, "requestedSupplierAction": string, "requestedCreditAmount": int, "supplierCreditAmount": null|int|float, "actualCreditAmount": null|int|float, "supplierName": string, "supplierNumber": string, "supplierErp": int, "parts": list<VendorWarrantyClaimPartStructure>, "id": int, "files": list<VendorWarrantyClaimFileStructure>, "warrantyClaimFiles": list<LegacyFileStructure>}
 *
 * @uses SimplifiedTypeStructureTrait<WCVendorWarrantyClaimStructure, SimplifiedWCVendorWarrantyClaimStructure>
 */
final class WCVendorWarrantyClaim implements VendorWarrantyClaimInterface
{
    use SimplifiedTypeStructureTrait;

    /**
     * @param list<VendorWarrantyClaimPart> $parts
     * @param list<Comment>                 $activities
     * @param list<VendorWarrantyClaimFile> $files
     * @param list<LegacyFile>              $warrantyClaimFiles
     * @param list<LegacyFile>              $tocFiles
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly VendorWarrantyClaimStatus $status,
        public readonly ?WarrantyClaim $warrantyClaim = null,
        public readonly ?int $warrantyClaimId = null,
        public readonly ?string $statusUpdatedAt = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $requestedSupplierAction = null,
        public readonly int|float|null $requestedCreditAmount = null,
        public readonly int|float|null $actualCreditAmount = null,
        public readonly ?string $supplierName = null,
        public readonly ?string $supplierNumber = null,
        public readonly ?int $supplierErp = null,
        public readonly ?array $parts = [],
        public readonly ?VendorWarrantyClaimType $type = null,
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
        public readonly ?VendorWarrantyClaimMainFile $mainFile = null,
        public readonly array $activities = [],
        public readonly array $files = [],
        public readonly array $warrantyClaimFiles = [],
        public readonly array $tocFiles = [],
        public readonly ?string $vendorToRespondAt = null,
    ) {
    }

    /**
     * @return Type\TypeInterface<WCVendorWarrantyClaimStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('WcVendorWarrantyClaim'),
            'id' => Type\int(),
            'status' => Type\union(VendorWarrantyClaimStatus::getTypeStructure(), Type\shape([
                '@id' => Type\non_empty_string(),
                '@type' => Type\literal_scalar('VendorWarrantyClaimStatus'),
                'name' => VendorWarrantyClaimStatus::getTypeStructure(),
                'description' => Type\string(),
                'position' => Type\int(),
            ])),
            'type' => Type\optional(Type\nullable(VendorWarrantyClaimType::getTypeStructure())),
            'warrantyClaimId' => Type\optional(Type\int()),
            'requestedSupplierAction' => Type\optional(Type\string()),
            'statusUpdatedAt' => Type\optional(Type\nullable(Type\string())),
            'createdAt' => Type\optional(Type\nullable(Type\string())),
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
            'shipBackDefectivePart' => Type\optional(Type\bool()),
            'scarRequested' => Type\optional(Type\bool()),
            'accepted' => Type\optional(Type\bool()),
            'activity' => Type\optional(Type\union(Type\vec(Comment::getTypeStructure()), Type\vec(Log::getLegacyTypeStructure()))),
            'files' => Type\optional(Type\vec(VendorWarrantyClaimFile::getTypeStructure())),
            'warrantyClaimFiles' => Type\optional(Type\vec(LegacyFile::getTypeStructure())),
            'tocFiles' => Type\optional(Type\vec(LegacyFile::getTypeStructure())),
            'supplierCorrectiveActionRequest' => Type\optional(Type\nullable(SupplierCorrectiveActionRequest::getSimplifiedTypeStructure())),
            'warrantyClaim' => Type\optional(WarrantyClaim::getTypeStructure()),
            'mainFile' => Type\optional(Type\nullable(VendorWarrantyClaimMainFile::getTypeStructure())),
            'vendorToRespondAt' => Type\optional(Type\nullable(Type\non_empty_string())),
        ], allow_unknown_fields: true);
    }

    /**
     * @return Type\TypeInterface<SimplifiedWCVendorWarrantyClaimStructure>
     */
    public static function getSimplifiedTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('WcVendorWarrantyClaim'),
            'id' => Type\int(),
            'status' => Type\union(VendorWarrantyClaimStatus::getTypeStructure(), Type\shape([
                '@id' => Type\non_empty_string(),
                '@type' => Type\literal_scalar('VendorWarrantyClaimStatus'),
                'name' => VendorWarrantyClaimStatus::getTypeStructure(),
                'description' => Type\string(),
                'position' => Type\int(),
            ])),
            'type' => Type\optional(Type\nullable(VendorWarrantyClaimType::getTypeStructure())),
            'warrantyClaimId' => Type\optional(Type\int()),
            'requestedSupplierAction' => Type\optional(Type\string()),
            'statusUpdatedAt' => Type\optional(Type\nullable(Type\string())),
            'createdAt' => Type\optional(Type\nullable(Type\string())),
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
            'shipBackDefectivePart' => Type\optional(Type\bool()),
            'scarRequested' => Type\optional(Type\bool()),
            'accepted' => Type\optional(Type\bool()),
            'activity' => Type\optional(Type\vec(Comment::getTypeStructure())),
            'files' => Type\optional(Type\vec(VendorWarrantyClaimFile::getTypeStructure())),
            'warrantyClaimFiles' => Type\optional(Type\vec(LegacyFile::getTypeStructure())),
            'tocFiles' => Type\optional(Type\vec(LegacyFile::getTypeStructure())),
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
        return self::WC_MODULE;
    }
}
