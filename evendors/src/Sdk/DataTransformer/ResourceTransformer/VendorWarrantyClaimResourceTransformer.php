<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\ActivityInterface;
use App\Sdk\Resource\Comment;
use App\Sdk\Resource\CommentFile;
use App\Sdk\Resource\Currency;
use App\Sdk\Resource\LegacyFile;
use App\Sdk\Resource\Location;
use App\Sdk\Resource\Log;
use App\Sdk\Resource\NCRVendorWarrantyClaim;
use App\Sdk\Resource\NonConformity;
use App\Sdk\Resource\NonConformityFile;
use App\Sdk\Resource\NonConformityMainFile;
use App\Sdk\Resource\NonConformityPart;
use App\Sdk\Resource\Person;
use App\Sdk\Resource\Process;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\Responsible;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;
use App\Sdk\Resource\SupplierCorrectiveActionRequestStatus;
use App\Sdk\Resource\VendorWarrantyClaimFile;
use App\Sdk\Resource\VendorWarrantyClaimInterface;
use App\Sdk\Resource\VendorWarrantyClaimMainFile;
use App\Sdk\Resource\VendorWarrantyClaimPart;
use App\Sdk\Resource\VendorWarrantyClaimStatus;
use App\Sdk\Resource\VendorWarrantyClaimType;
use App\Sdk\Resource\WarrantyClaim;
use App\Sdk\Resource\WCVendorWarrantyClaim;
use DateTime;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Type;
use Psl\Vec;

/**
 * @implements ResourceTransformerInterface<VendorWarrantyClaimInterface>
 */
final class VendorWarrantyClaimResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        if (!is_a($resource, VendorWarrantyClaimInterface::class, true)) {
            return false;
        }

        return Type\union(
            NCRVendorWarrantyClaim::getTypeStructure(),
            WCVendorWarrantyClaim::getTypeStructure(),
        )->matches($data);
    }

    public function transform(mixed $data): ResourceInterface
    {
        try {
            $data = Type\union(
                NCRVendorWarrantyClaim::getTypeStructure(),
                WCVendorWarrantyClaim::getTypeStructure(),
            )->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a VWC structure.', previous: $e);
        }

        return $this->getMember($data);
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        if (VendorWarrantyClaimInterface::class !== $resource) {
            return false;
        }

        return NCRVendorWarrantyClaim::getCollectionTypeStructureOf(Type\union(
            NCRVendorWarrantyClaim::getTypeStructure(),
            WCVendorWarrantyClaim::getSimplifiedTypeStructure(),
        ))->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = NCRVendorWarrantyClaim::getCollectionTypeStructureOf(Type\union(
                NCRVendorWarrantyClaim::getTypeStructure(),
                WCVendorWarrantyClaim::getSimplifiedTypeStructure(),
            ))->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list VWC structures.', previous: $e);
        }

        $members = [];
        foreach ($collection['hydra:member'] as $member) {
            $members[] = $this->getMember($member);
        }

        return Vector::fromArray($members);
    }

    public function getMember(mixed $data): VendorWarrantyClaimInterface
    {
        // parts are common between WC-VWC and NCR-VWC, map once.
        $parts = Vec\map(
            $data['parts'],
            static fn (array $structure): VendorWarrantyClaimPart => new VendorWarrantyClaimPart(
                iri: $structure['@id'],
                id: $structure['id'],
                serialNumber: $structure['serialNumber'],
                vendorPartNumber: $structure['vendorPartNumber'],
                vendorSerialNumber: $structure['vendorSerialNumber'],
                failureType: $structure['failureType'],
                failureSystem: $structure['failureSystem'],
                ship: $structure['ship'],
                receivedQuantity: $structure['receivedQuantity'],
                createdAt: $structure['createdAt'],
                createdBy: new Person(
                    iri: $structure['createdBy']['@id'],
                    username: $structure['createdBy']['username'],
                    email: $structure['createdBy']['email'],
                    firstname: $structure['createdBy']['firstname'],
                    lastname: $structure['createdBy']['lastname'],
                ),
                partNumber: $structure['partNumber'],
                description: $structure['description'],
                quantity: $structure['quantity'],
                unitOfMeasure: $structure['unitOfMeasure'],
                deletedAt: $structure['deletedAt'],
                deletedBy: null !== $structure['deletedBy'] ? new Person(
                    iri: $structure['deletedBy']['@id'],
                    username: $structure['deletedBy']['username'],
                    email: $structure['deletedBy']['email'],
                    firstname: $structure['deletedBy']['firstname'],
                    lastname: $structure['deletedBy']['lastname'],
                ) : null,
            ),
        );

        $activities = Vec\map($data['activity'] ?? [], static function ($activity) {
            switch (true) {
                case ActivityInterface::COMMENT_TYPE === $activity['@type']:
                    return new Comment(
                        iri: $activity['@id'],
                        resource: $activity['resource'],
                        message: $activity['message'] ?? null,
                        user: (null === $activity['user']) ? null : new Person(
                            iri: $activity['user']['@id'],
                            username: $activity['user']['username'],
                            email: $activity['user']['email'],
                            firstname: $activity['user']['firstname'],
                            lastname: $activity['user']['lastname'],
                        ),
                        createdAt: $activity['createdAt'],
                        updatedAt: $activity['updatedAt'],
                        public: $activity['public'],
                        metadata: $activity['metadata'] ?? [],
                        files: ($activity['files'] ?? null) === null ? [] : Vec\map($activity['files'], static fn ($file) => new CommentFile(
                            iri: $file['@id'],
                            id: $file['id'],
                            filePath: $file['filePath'],
                            createdAt: $file['createdAt'],
                        )),
                    );
                case ActivityInterface::LOG_TYPE === $activity['@type']:
                    return new Log(
                        iri: $activity['@id'],
                        resource: $activity['resource'],
                        user: (null === $activity['user']) ? null : new Person(
                            iri: $activity['user']['@id'],
                            username: $activity['user']['username'],
                            email: $activity['user']['email'],
                            firstname: $activity['user']['firstname'],
                            lastname: $activity['user']['lastname'],
                        ),
                        createdAt: $activity['createdAt'],
                        updatedAt: $activity['updatedAt'],
                        public: $activity['public'],
                        changeSet: $activity['changeSet'] ?? [],
                        metadata: $activity['metadata'] ?? [],
                    );
            }
        });

        $status = VendorWarrantyClaimStatus::from(
            Type\string()->matches($data['status']) ? $data['status'] : $data['status']['name']
        );

        $files = ($data['files'] ?? null) === null ? [] : Vec\map($data['files'], static fn ($file) => new VendorWarrantyClaimFile(
            iri: $file['@id'],
            id: $file['id'],
            filePath: $file['filePath'],
            poster: (null === $file['poster']) ? null : new Person(
                iri: $file['poster']['@id'],
                username: $file['poster']['username'],
                email: $file['poster']['email'],
                firstname: $file['poster']['firstname'],
                lastname: $file['poster']['lastname'],
            ),
            createdAt: $file['createdAt'],
            description: $file['description'],
            sha: $file['sha'],
            mimeType: $file['mimeType'],
            extension: $file['extension'],
            size: $file['size'],
            public: $file['public'],
        ));

        $warrantyClaimFiles = ($data['warrantyClaimFiles'] ?? null) === null ? [] : Vec\map($data['warrantyClaimFiles'], static fn ($file) => new LegacyFile(
            iri: $file['@id'],
            id: $file['id'],
            filePath: $file['filePath'],
            poster: (null === $file['poster']) ? null : new Person(
                iri: $file['poster']['@id'],
                username: $file['poster']['username'],
                email: $file['poster']['email'],
                firstname: $file['poster']['firstname'],
                lastname: $file['poster']['lastname'],
            ),
            createdAt: $file['createdAt'],
            description: $file['description'],
        ));

        $tocFiles = ($data['tocFiles'] ?? null) === null ? [] : Vec\map($data['tocFiles'], static fn ($file) => new LegacyFile(
            iri: $file['@id'],
            id: $file['id'],
            filePath: $file['filePath'],
            poster: (null === $file['poster']) ? null : new Person(
                iri: $file['poster']['@id'],
                username: $file['poster']['username'],
                email: $file['poster']['email'],
                firstname: $file['poster']['firstname'],
                lastname: $file['poster']['lastname'],
            ),
            createdAt: $file['createdAt'],
            description: $file['description'],
        ));

        $supplierCorrectiveActionRequest = ($data['supplierCorrectiveActionRequest'] ?? null) === null ? null : new SupplierCorrectiveActionRequest(
            iri: $data['supplierCorrectiveActionRequest']['@id'],
            id: $data['supplierCorrectiveActionRequest']['id'],
            poster: (!$data['supplierCorrectiveActionRequest']['poster']) ? null : new Person(
                iri: $data['supplierCorrectiveActionRequest']['poster']['@id'],
                username: $data['supplierCorrectiveActionRequest']['poster']['username'],
                email: $data['supplierCorrectiveActionRequest']['poster']['email'],
                firstname: $data['supplierCorrectiveActionRequest']['poster']['firstname'],
                lastname: $data['supplierCorrectiveActionRequest']['poster']['lastname'],
            ),
            leader: null === $data['supplierCorrectiveActionRequest']['leader'] ? null : new Person(
                iri: $data['supplierCorrectiveActionRequest']['leader']['@id'],
                username: $data['supplierCorrectiveActionRequest']['leader']['username'],
                email: $data['supplierCorrectiveActionRequest']['leader']['email'],
                firstname: $data['supplierCorrectiveActionRequest']['leader']['firstname'],
                lastname: $data['supplierCorrectiveActionRequest']['leader']['lastname'],
            ),
            iFactor: $data['supplierCorrectiveActionRequest']['iFactor'],
            factory: new Location(
                iri: $data['supplierCorrectiveActionRequest']['factory']['@id'],
                name: $data['supplierCorrectiveActionRequest']['factory']['name'],
                erp: $data['supplierCorrectiveActionRequest']['factory']['erp'],
            ),
            supplierErp: $data['supplierCorrectiveActionRequest']['supplierErp'],
            supplierNumber: $data['supplierCorrectiveActionRequest']['supplierNumber'],
            supplierName: $data['supplierCorrectiveActionRequest']['supplierName'],
            status: SupplierCorrectiveActionRequestStatus::from($data['supplierCorrectiveActionRequest']['status']),
            createdAt: $data['supplierCorrectiveActionRequest']['createdAt'],
        );

        if ('WcVendorWarrantyClaim' === $data['@type']) {
            return new WCVendorWarrantyClaim(
                iri: $data['@id'],
                id: $data['id'],
                status: $status,
                warrantyClaim: ($data['warrantyClaim'] ?? null) === null ? null : new WarrantyClaim(
                    id: (int) $data['warrantyClaim']['id'],
                    status: $data['warrantyClaim']['status'],
                    enteredBy: $data['warrantyClaim']['enteredBy'],
                    warrantyDetails: $data['warrantyClaim']['warrantyDetails'],
                    customer: $data['warrantyClaim']['customer'],
                    problemDescription: $data['warrantyClaim']['problemDescription'],
                    claimDate: $data['warrantyClaim']['claimDate'],
                    type: $data['warrantyClaim']['type'],
                    model: $data['warrantyClaim']['model'],
                    manufacturerLocation: $data['warrantyClaim']['manufacturerLocation'],
                    salesOrganization: $data['warrantyClaim']['salesOrganization'],
                    serialNumber: $data['warrantyClaim']['serialNumber'],
                    equipmentLocation: $data['warrantyClaim']['equipmentLocation'],
                    hours: (int) $data['warrantyClaim']['hours'],
                    claimantDetails: $data['warrantyClaim']['claimantDetails'] ?? null,
                ),
                warrantyClaimId: $data['warrantyClaimId'],
                statusUpdatedAt: $data['statusUpdatedAt'] ?? null,
                createdAt: $data['createdAt'] ?? null,
                requestedSupplierAction: $data['requestedSupplierAction'] ?? null,
                requestedCreditAmount: $data['requestedCreditAmount'] ?? null,
                actualCreditAmount: $data['actualCreditAmount'] ?? null,
                supplierName: $data['supplierName'] ?? null,
                supplierNumber: $data['supplierNumber'] ?? null,
                supplierErp: $data['supplierErp'] ?? null,
                parts: $parts,
                type: ($data['type'] ?? null) === null ? null : new VendorWarrantyClaimType(
                    iri: $data['type']['@id'],
                    name: $data['type']['name'],
                    description: $data['type']['description'],
                ),
                poster: ($data['poster'] ?? null) === null ? null : new Person(
                    iri: $data['poster']['@id'],
                    username: $data['poster']['username'],
                    email: $data['poster']['email'],
                    firstname: $data['poster']['firstname'],
                    lastname: $data['poster']['lastname'],
                ),
                assignee: $data['assignee'] ? new Person(
                    iri: $data['assignee']['@id'],
                    username: $data['assignee']['username'],
                    email: $data['assignee']['email'],
                    firstname: $data['assignee']['firstname'],
                    lastname: $data['assignee']['lastname'],
                ) : null,
                location: ($data['location'] ?? null) === null ? null : new Location(
                    iri: $data['location']['@id'],
                    name: $data['location']['name'],
                    erp: $data['location']['erp'],
                ),
                supplierReturnMerchandiseAuthorization: $data['supplierReturnMerchandiseAuthorization'] ?? null,
                supplierShippingInstruction: $data['supplierShippingInstruction'] ?? null,
                supplierShipperName: $data['supplierShipperName'] ?? null,
                trackingNumber: $data['trackingNumber'] ?? null,
                supplierCreditAmount: $data['supplierCreditAmount'] ?? null,
                supplierCreditNote: $data['supplierCreditNote'] ?? null,
                shipBackDefectivePart: $data['shipBackDefectivePart'] ?? null,
                scarRequested: $data['scarRequested'] ?? null,
                accepted: $data['accepted'] ?? null,
                supplierCorrectiveActionRequest: $supplierCorrectiveActionRequest,
                mainFile: ($data['mainFile'] ?? null) === null ? null : new VendorWarrantyClaimMainFile(
                    iri: $data['mainFile']['@id'],
                    id: $data['mainFile']['id'],
                    filePath: $data['mainFile']['filePath'],
                    poster: (null === $data['mainFile']['poster']) ? null : new Person(
                        iri: $data['mainFile']['poster']['@id'],
                        username: $data['mainFile']['poster']['username'],
                        email: $data['mainFile']['poster']['email'],
                        firstname: $data['mainFile']['poster']['firstname'],
                        lastname: $data['mainFile']['poster']['lastname'],
                    ),
                    createdAt: $data['mainFile']['createdAt'],
                    description: $data['mainFile']['description'],
                    sha: $data['mainFile']['sha'],
                    mimeType: $data['mainFile']['mimeType'],
                    extension: $data['mainFile']['extension'],
                    size: $data['mainFile']['size'],
                    public: $data['mainFile']['public']
                ),
                activities: $activities,
                files: $files,
                warrantyClaimFiles: $warrantyClaimFiles,
                tocFiles: $tocFiles,
                vendorToRespondAt: isset($data['vendorToRespondAt']) ? (new DateTime($data['vendorToRespondAt']))->format('Y-m-d') : null,
            );
        }

        return new NCRVendorWarrantyClaim(
            iri: $data['@id'],
            id: $data['id'],
            nonConformity: new NonConformity(
                iri: $data['nonConformity']['@id'],
                id: $data['nonConformity']['id'],
                problem: $data['nonConformity']['problem'] ?? null,
                location: ($data['nonConformity']['location'] ?? null) !== null ? new Location(
                    iri: $data['nonConformity']['location']['@id'],
                    name: $data['nonConformity']['location']['name'],
                    erp: $data['nonConformity']['location']['erp'],
                    currency: ($data['nonConformity']['location']['currency'] ?? null) === null ? null : new Currency(
                        $data['nonConformity']['location']['currency']['@id'],
                        $data['nonConformity']['location']['currency']['id'],
                        $data['nonConformity']['location']['currency']['name'],
                    ),
                ) : null,
                processes: Vec\map($data['nonConformity']['processes'] ?? [], static function ($process) {
                    return new Process(
                        $process['@id'],
                        $process['category'],
                        $process['description'],
                    );
                }),
                createdAt: $data['nonConformity']['createdAt'] ?? null,
                status: $data['nonConformity']['status'] ?? null,
                shortDescription: $data['nonConformity']['shortDescription'] ?? null,
                reportedBy: ($data['nonConformity']['reportedBy'] ?? null) !== null ? new Person(
                    iri: $data['nonConformity']['reportedBy']['@id'],
                    username: $data['nonConformity']['reportedBy']['username'],
                    email: $data['nonConformity']['reportedBy']['email'],
                    firstname: $data['nonConformity']['reportedBy']['firstname'],
                    lastname: $data['nonConformity']['reportedBy']['lastname'],
                ) : null,
                hours: $data['nonConformity']['hours'] ?? null,
                supplier: $data['nonConformity']['supplier'] ?? null,
                solution: $data['nonConformity']['solution'] ?? null,
                responsibles: Vec\map($data['nonConformity']['responsibles'] ?? [], static function ($responsible) {
                    return new Responsible(
                        $responsible['@id'],
                        $responsible['name'],
                    );
                }),
                purchaseOrderNumber: $data['nonConformity']['purchaseOrderNumber'] ?? null,
                rush: $data['nonConformity']['rush'] ?? null,
                chargeVendor: $data['nonConformity']['chargeVendor'] ?? null,
                failureType: $data['nonConformity']['failureType'] ?? null,
                iFactor: $data['nonConformity']['iFactor'] ?? null,
                investigation: $data['nonConformity']['investigation'] ?? null,
                scrap: $data['nonConformity']['scrap'] ?? null,
                rework: $data['nonConformity']['rework'] ?? null,
                firstArticleInspection: $data['nonConformity']['firstArticleInspection'] ?? null,
                useAsIs: $data['nonConformity']['useAsIs'] ?? null,
                derogation: $data['nonConformity']['derogation'] ?? null,
                returnVendor: $data['nonConformity']['returnVendor'] ?? null,
                chargeVendorForRepair: $data['nonConformity']['chargeVendorForRepair'] ?? null,
                supplierCorrectiveActionRequest: $data['nonConformity']['supplierCorrectiveActionRequest'] ?? null,
                internalCorrectiveActionRequest: $data['nonConformity']['internalCorrectiveActionRequest'] ?? null,
                other: $data['nonConformity']['other'] ?? null,
                containment: $data['nonConformity']['containment'] ?? null,
                environmentalIssue: $data['nonConformity']['environmentalIssue'] ?? null,
                actionComment: $data['nonConformity']['actionComment'] ?? null,
                currency: ($data['nonConformity']['currency'] ?? null) === null ? null : new Currency(
                    $data['nonConformity']['currency']['@id'],
                    $data['nonConformity']['currency']['id'],
                    $data['nonConformity']['currency']['name'],
                ),
                cost: $data['nonConformity']['cost'] ?? null,
                costBreakdown: $data['nonConformity']['costBreakdown'] ?? null,
                nonQualityCost: $data['nonConformity']['nonQualityCost'] ?? null,
                supplierName: $data['nonConformity']['supplierName'] ?? null,
                supplierNumber: $data['nonConformity']['supplierNumber'] ?? null,
                supplierErp: $data['nonConformity']['supplierErp'] ?? null,
                invoiceNumber: $data['nonConformity']['invoiceNumber'] ?? null,
                mainFile: ($data['nonConformity']['mainFile'] ?? null) === null ? null : new NonConformityMainFile(
                    iri: $data['nonConformity']['mainFile']['@id'],
                    id: $data['nonConformity']['mainFile']['id'],
                    filePath: $data['nonConformity']['mainFile']['filePath'],
                    poster: (null === $data['nonConformity']['mainFile']['poster']) ? null : new Person(
                        iri: $data['nonConformity']['mainFile']['poster']['@id'],
                        username: $data['nonConformity']['mainFile']['poster']['username'],
                        email: $data['nonConformity']['mainFile']['poster']['email'],
                        firstname: $data['nonConformity']['mainFile']['poster']['firstname'],
                        lastname: $data['nonConformity']['mainFile']['poster']['lastname'],
                    ),
                    createdAt: $data['nonConformity']['mainFile']['createdAt'],
                    description: $data['nonConformity']['mainFile']['description'],
                    sha: $data['nonConformity']['mainFile']['sha'],
                    mimeType: $data['nonConformity']['mainFile']['mimeType'],
                    extension: $data['nonConformity']['mainFile']['extension'],
                    size: $data['nonConformity']['mainFile']['size'],
                    public: $data['nonConformity']['mainFile']['public']
                ),
                files: ($data['nonConformity']['files'] ?? null) === null ? [] : Vec\map($data['nonConformity']['files'], static fn ($file) => new NonConformityFile(
                    iri: $file['@id'],
                    id: $file['id'],
                    filePath: $file['filePath'],
                    poster: (null === $file['poster']) ? null : new Person(
                        iri: $file['poster']['@id'],
                        username: $file['poster']['username'],
                        email: $file['poster']['email'],
                        firstname: $file['poster']['firstname'],
                        lastname: $file['poster']['lastname'],
                    ),
                    createdAt: $file['createdAt'],
                    description: $file['description'],
                    sha: $file['sha'],
                    mimeType: $file['mimeType'],
                    extension: $file['extension'],
                    size: $file['size'],
                    public: $file['public'],
                )),
                parts: Vec\map($data['nonConformity']['parts'] ?? [], static function ($part) {
                    return new NonConformityPart(
                        $part['@id'],
                        $part['id'],
                        $part['reference'],
                        $part['referenceNumber'],
                        $part['serialNumber'],
                        $part['createdAt'],
                        null === $part['createdBy'] ? null : new Person(
                            $part['createdBy']['@id'],
                            $part['createdBy']['username'],
                            $part['createdBy']['email'],
                            $part['createdBy']['firstname'],
                            $part['createdBy']['lastname'],
                        ),
                        $part['partNumber'],
                        $part['description'],
                        $part['quantity'],
                        $part['unitOfMeasure'],
                        $part['deletedAt'],
                        null === $part['deletedBy'] ? null : new Person(
                            $part['deletedBy']['@id'],
                            $part['deletedBy']['username'],
                            $part['deletedBy']['email'],
                            $part['deletedBy']['firstname'],
                            $part['deletedBy']['lastname'],
                        ),
                    );
                }),
            ),
            type: ($data['type'] ?? null) === null ? null : new VendorWarrantyClaimType(
                iri: $data['type']['@id'],
                name: $data['type']['name'],
                description: $data['type']['description'],
            ),
            status: $status,
            statusUpdatedAt: $data['statusUpdatedAt'] ?? null,
            createdAt: $data['createdAt'] ?? null,
            requestedSupplierAction: $data['requestedSupplierAction'] ?? null,
            requestedCreditAmount: $data['requestedCreditAmount'] ?? null,
            actualCreditAmount: $data['actualCreditAmount'] ?? null,
            supplierName: $data['supplierName'] ?? null,
            supplierNumber: $data['supplierNumber'] ?? null,
            supplierErp: $data['supplierErp'] ?? null,
            parts: $parts,
            poster: ($data['poster'] ?? null) === null ? null : new Person(
                iri: $data['poster']['@id'],
                username: $data['poster']['username'],
                email: $data['poster']['email'],
                firstname: $data['poster']['firstname'],
                lastname: $data['poster']['lastname'],
            ),
            assignee: null === $data['assignee'] ? null : new Person(
                iri: $data['assignee']['@id'],
                username: $data['assignee']['username'],
                email: $data['assignee']['email'],
                firstname: $data['assignee']['firstname'],
                lastname: $data['assignee']['lastname'],
            ),
            location: ($data['location'] ?? null) === null ? null : new Location(
                iri: $data['location']['@id'],
                name: $data['location']['name'],
                erp: $data['location']['erp'],
            ),
            supplierReturnMerchandiseAuthorization: $data['supplierReturnMerchandiseAuthorization'] ?? null,
            supplierShippingInstruction: $data['supplierShippingInstruction'] ?? null,
            supplierShipperName: $data['supplierShipperName'] ?? null,
            trackingNumber: $data['trackingNumber'] ?? null,
            supplierCreditAmount: $data['supplierCreditAmount'] ?? null,
            supplierCreditNote: $data['supplierCreditNote'] ?? null,
            shipBackDefectivePart: $data['shipBackDefectivePart'] ?? null,
            scarRequested: $data['scarRequested'] ?? null,
            accepted: $data['accepted'] ?? null,
            supplierCorrectiveActionRequest: $supplierCorrectiveActionRequest,
            activities: $activities,
            files: $files,
            vendorToRespondAt: isset($data['vendorToRespondAt']) ? (new DateTime($data['vendorToRespondAt']))->format('Y-m-d') : null,
        );
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        throw new FailedTransformationException('Pagination is not supported for VWC resource.');
    }
}
