<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\SimplifiedTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-import-type PersonStructure from Person
 *
 * @psalm-import-type PersonStructure from Person
 *
 * @phpstan-import-type LocationStructure from Location
 *
 * @psalm-import-type LocationStructure from Location
 *
 * @phpstan-import-type VendorUserStructure from VendorUser
 *
 * @psalm-import-type VendorUserStructure from VendorUser
 *
 * @phpstan-import-type NCRVendorWarrantyClaimStructure from NCRVendorWarrantyClaim
 *
 * @psalm-import-type NCRVendorWarrantyClaimStructure from NCRVendorWarrantyClaim
 *
 * @phpstan-import-type WCVendorWarrantyClaimStructure from WCVendorWarrantyClaim
 *
 * @psalm-import-type WCVendorWarrantyClaimStructure from WCVendorWarrantyClaim
 *
 * @phpstan-import-type SupplierCorrectiveActionRequestPartStructure from SupplierCorrectiveActionRequestPart
 *
 * @psalm-import-type SupplierCorrectiveActionRequestPartStructure from SupplierCorrectiveActionRequestPart
 *
 * @phpstan-import-type SupplierCorrectiveActionRequestFileStructure from SupplierCorrectiveActionRequestFile
 *
 * @psalm-import-type SupplierCorrectiveActionRequestFileStructure from SupplierCorrectiveActionRequestFile
 *
 * @phpstan-import-type SupplierCorrectiveActionRequestMainFileStructure from SupplierCorrectiveActionRequestMainFile
 *
 * @psalm-import-type SupplierCorrectiveActionRequestMainFileStructure from SupplierCorrectiveActionRequestMainFile
 *
 * @phpstan-import-type CommentStructure from Comment
 *
 * @psalm-import-type CommentStructure from Comment
 *
 * @phpstan-type SupplierCorrectiveActionRequestStructure array{"@id": non-empty-string, "@type": "SupplierCorrectiveActionRequest", "id": positive-int, "conclusion": null|string, "preventiveAction": null|string, "commercialAgreement": null|string, "verificationDescription": null|string, "correctiveAction": null|string, "issueOrigin": null|string, "shortDescription": null|string, "description": null|string, "createdAt": non-empty-string, "approvedAt": null|non-empty-string, "closedAt": null|non-empty-string, "supplierRepresentative": ?VendorUserStructure, "representative": ?PersonStructure, "poster": ?PersonStructure, "leader": ?PersonStructure, "iFactor": string, "factory": LocationStructure, "supplierErp": null|positive-int, "supplierNumber": string, "supplierName": string, "status": "CLOSED"|"PENDING"|"VENDOR TO FILL FORM"|"TLD TO FILL FORM"|"VALIDATION"|"CANCEL", "vendorWarrantyClaims": list<NCRVendorWarrantyClaimStructure|WCVendorWarrantyClaimStructure>, "parts": list<SupplierCorrectiveActionRequestPartStructure>, "files": list<SupplierCorrectiveActionRequestFileStructure>, "mainFile": null|SupplierCorrectiveActionRequestMainFileStructure, "activity": list<CommentStructure>}
 *
 * @psalm-type SupplierCorrectiveActionRequestStructure = array{"@id": non-empty-string, "@type": "SupplierCorrectiveActionRequest", "id": positive-int, "conclusion": null|string, "preventiveAction": null|string, "commercialAgreement": null|string, "verificationDescription": null|string, "correctiveAction": null|string, "issueOrigin": null|string, "shortDescription": null|string, "description": null|string, "createdAt": non-empty-string, "approvedAt": null|non-empty-string, "closedAt": null|non-empty-string, "supplierRepresentative": ?VendorUserStructure, "representative": ?PersonStructure, "poster": ?PersonStructure, "leader": ?PersonStructure, "iFactor": string, "factory": LocationStructure, "supplierErp": null|positive-int, "supplierNumber": string, "supplierName": string "status": "CLOSED"|"PENDING"|"VENDOR TO FILL FORM"|"TLD TO FILL FORM"|"VALIDATION"|"CANCEL", "vendorWarrantyClaims": list<NCRVendorWarrantyClaimStructure|WCVendorWarrantyClaimStructure>, "parts": list<SupplierCorrectiveActionRequestPartStructure>, "files": list<SupplierCorrectiveActionRequestFileStructure>, "mainFile": null|SupplierCorrectiveActionRequestMainFileStructure, "activity": list<CommentStructure>}
 *
 * @phpstan-type SimplifiedSupplierCorrectiveActionRequestStructure array{"@id": non-empty-string, "@type": "SupplierCorrectiveActionRequest", "id": positive-int, "createdAt": non-empty-string, "poster": ?PersonStructure, "leader": ?PersonStructure, "iFactor": string, "factory": LocationStructure, "supplierErp": null|positive-int, "supplierNumber": string, "supplierName": non-empty-string, "status": "CLOSED"|"PENDING"|"VENDOR TO FILL FORM"|"TLD TO FILL FORM"|"VALIDATION"|"CANCEL"}
 *
 * @psalm-type SimplifiedSupplierCorrectiveActionRequestStructure = array{"@id": non-empty-string, "@type": "SupplierCorrectiveActionRequest", "id": positive-int, "createdAt": non-empty-string, "poster": ?PersonStructure, "leader": ?PersonStructure, "iFactor": string, "factory": LocationStructure, "supplierErp": null|positive-int, "supplierNumber": string, "supplierName": non-empty-string, "status": "CLOSED"|"PENDING"|"VENDOR TO FILL FORM"|"TLD TO FILL FORM"|"VALIDATION"|"CANCEL"}
 *
 * @uses SimplifiedTypeStructureTrait<SupplierCorrectiveActionRequestStructure, SimplifiedSupplierCorrectiveActionRequestStructure>
 */
final class SupplierCorrectiveActionRequest implements ResourceInterface
{
    use SimplifiedTypeStructureTrait;

    /**
     * @param list<NCRVendorWarrantyClaim>              $vendorWarrantyClaims
     * @param list<SupplierCorrectiveActionRequestPart> $parts
     * @param list<SupplierCorrectiveActionRequestFile> $files
     * @param list<Comment>                             $activities
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly ?Person $poster,
        public readonly ?Person $leader,
        public readonly string $iFactor,
        public readonly Location $factory,
        public readonly ?int $supplierErp,
        public readonly string $supplierNumber,
        public readonly string $supplierName,
        public readonly SupplierCorrectiveActionRequestStatus $status,
        public readonly string $createdAt,
        public readonly ?string $approvedAt = null,
        public readonly ?string $closedAt = null,
        public readonly ?string $description = null,
        public readonly ?string $shortDescription = null,
        public readonly ?string $issueOrigin = null,
        public readonly ?string $correctiveAction = null,
        public readonly ?string $commercialAgreement = null,
        public readonly ?Person $representative = null,
        public readonly ?VendorUser $supplierRepresentative = null,
        public readonly ?string $verificationDescription = null,
        public readonly ?string $preventiveAction = null,
        public readonly ?string $conclusion = null,
        public readonly array $vendorWarrantyClaims = [],
        public readonly array $parts = [],
        public readonly array $files = [],
        public readonly ?SupplierCorrectiveActionRequestMainFile $mainFile = null,
        public readonly array $activities = [],
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<SupplierCorrectiveActionRequestStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('SupplierCorrectiveActionRequest'),
            'id' => Type\positive_int(),
            'description' => Type\nullable(Type\string()),
            'shortDescription' => Type\nullable(Type\string()),
            'issueOrigin' => Type\nullable(Type\string()),
            'correctiveAction' => Type\nullable(Type\string()),
            'commercialAgreement' => Type\nullable(Type\string()),
            'verificationDescription' => Type\nullable(Type\string()),
            'preventiveAction' => Type\nullable(Type\string()),
            'conclusion' => Type\nullable(Type\string()),
            'mainFile' => Type\optional(Type\nullable(SupplierCorrectiveActionRequestMainFile::getTypeStructure())),
            'createdAt' => Type\non_empty_string(),
            'approvedAt' => Type\nullable(Type\non_empty_string()),
            'closedAt' => Type\nullable(Type\non_empty_string()),
            'poster' => Type\nullable(Person::getTypeStructure()),
            'representative' => Type\nullable(Person::getTypeStructure()),
            'supplierRepresentative' => Type\nullable(VendorUser::getTypeStructure()),
            'leader' => Type\nullable(Person::getTypeStructure()),
            'iFactor' => Type\string(),
            'factory' => Location::getTypeStructure(),
            'supplierErp' => Type\optional(Type\nullable(Type\positive_int())),
            'supplierNumber' => Type\string(),
            'supplierName' => Type\string(),
            'status' => SupplierCorrectiveActionRequestStatus::getTypeStructure(),
            'vendorWarrantyClaims' => Type\vec(
                Type\union(
                    NCRVendorWarrantyClaim::getTypeStructure(),
                    WCVendorWarrantyClaim::getTypeStructure(),
                )
            ),
            'parts' => Type\vec(SupplierCorrectiveActionRequestPart::getTypeStructure()),
            'files' => Type\vec(SupplierCorrectiveActionRequestFile::getTypeStructure()),
            'activity' => Type\vec(Comment::getTypeStructure()),
        ], allow_unknown_fields: true);
    }

    /**
     * @return Type\TypeInterface<SimplifiedSupplierCorrectiveActionRequestStructure>
     */
    public static function getSimplifiedTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('SupplierCorrectiveActionRequest'),
            'id' => Type\positive_int(),
            'createdAt' => Type\non_empty_string(),
            'poster' => Type\nullable(Person::getTypeStructure()),
            'leader' => Type\nullable(Person::getTypeStructure()),
            'iFactor' => Type\string(),
            'factory' => Location::getTypeStructure(),
            'supplierErp' => Type\optional(Type\nullable(Type\positive_int())),
            'supplierNumber' => Type\string(),
            'supplierName' => Type\string(),
            'status' => SupplierCorrectiveActionRequestStatus::getTypeStructure(),
        ], allow_unknown_fields: true);
    }
}
