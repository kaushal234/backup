<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\DataTransformer\ResourceTransformer\VendorWarrantyClaimResourceTransformer;
use App\Sdk\Resource\NCRVendorWarrantyClaim;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\VendorWarrantyClaimInterface;

use function count;

/**
 * @extends AbstractResourceTransformerTest<NCRVendorWarrantyClaim>
 *
 * @group unit
 */
final class NCRVendorWarrantyClaimResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        // #0 with all data
        yield [[
            '@id' => '/vendorwarrantyclaims/1',
            '@type' => 'NcrVendorWarrantyClaim',
            'nonConformity' => [
                '@id' => '/nonconformities/1',
                '@type' => 'NonConformity',
                'id' => 1,
                'location' => [
                    '@id' => '/location/1',
                    '@type' => 'Location',
                    'name' => 'some name',
                    'erp' => 315,
                    'currency' => [
                        '@id' => '/currencies/1',
                        '@type' => 'Currency',
                        'id' => 1,
                        'name' => 'some currency name',
                    ],
                ],
                'process' => [
                    '@id' => '/processes/1',
                    '@type' => 'Process',
                    'category' => 'some category',
                    'description' => 'some description',
                ],
                'createdAt' => 'some createdAt',
                'status' => 'some status',
                'reportedBy' => [
                    '@id' => '/persons/2',
                    'username' => 'some username',
                    'email' => 'some email',
                    'firstname' => 'some firstname',
                    'lastname' => 'some lastname',
                ],
                'hours' => 1,
                'supplier' => 'some supplier',
                'problem' => 'some problem',
                'shortDescription' => 'some shortDescription',
                'solution' => 'some solution',
                'responsible' => 'some responsible',
                'purchaseOrderNumber' => 'purchaseOrderNumber',
                'rush' => true,
                'chargeVendor' => true,
                'failureType' => 'some failure',
                'iFactor' => 'some iFactor',
                'investigation' => 'some investigation',
                'scrap' => true,
                'rework' => true,
                'firstArticleInspection' => true,
                'useAsIs' => true,
                'derogation' => true,
                'returnVendor' => true,
                'chargeVendorForRepair' => true,
                'supplierCorrectiveActionRequest' => true,
                'internalCorrectiveActionRequest' => true,
                'other' => true,
                'containment' => true,
                'environmentalIssue' => true,
                'actionComment' => 'some actionComment',
                'currency' => [
                    '@id' => '/currencies/1',
                    '@type' => 'Currency',
                    'id' => 1,
                    'name' => 'some currency name',
                ],
                'cost' => 10,
                'costBreakdown' => 'some costBreakdown',
                'nonQualityCost' => 11,
                'supplierName' => 'some supplierName',
                'supplierNumber' => 'some supplierNumber',
                'supplierErp' => 316,
                'invoiceNumber' => 'some invoiceNumber',
                'mainFile' => [
                    '@id' => '/nonconformitymainfile/1',
                    '@type' => 'NonConformityMainFile',
                    'id' => 1,
                    'filePath' => 'some filePath',
                    'poster' => [
                        '@id' => '/person/1',
                        'username' => 'some_username',
                        'email' => 'some_email',
                        'firstname' => 'some_firstname',
                        'lastname' => 'some_lastname',
                    ],
                    'createdAt' => 'some createdAt',
                    'description' => 'some description',
                    'sha' => 'some sha',
                    'mimeType' => 'some mimeType',
                    'extension' => 'some extension',
                    'size' => 256000,
                    'public' => false,
                ],
                'parts' => [[
                    '@id' => '/NonConformityPart/1',
                    '@type' => 'NonConformityPart',
                    'reference' => 'some reference',
                    'referenceNumber' => 'some referenceNumber',
                    'serialNumber' => 'some serialNumber',
                    'createdAt' => 'some createdAt',
                    'createdBy' => [
                        '@id' => '/persons/4',
                        'username' => 'some username',
                        'email' => 'some email',
                        'firstname' => 'some firstname',
                        'lastname' => 'some lastname',
                    ],
                    'partNumber' => 'some partNumber',
                    'description' => 'some description',
                    'quantity' => 1,
                    'unitOfMeasure' => 'some unitOfMeasure',
                    'deletedAt' => 'some deletedAt',
                    'deletedBy' => [
                        '@id' => '/persons/1',
                        'username' => 'some username',
                        'email' => 'some email',
                        'firstname' => 'some firstname',
                        'lastname' => 'some lastname',
                    ],
                    'id' => 1,
                ],
                ], ],
            'type' => [
                '@id' => '/vendorwarrantyclaimtypes/1',
                '@type' => 'VendorWarrantyClaimType',
                'name' => 'some name',
                'description' => 'some description',
            ],
            'id' => 1,
            'status' => 'PENDING',
            'statusUpdatedAt' => 'some statusUpdatedAt',
            'createdAt' => 'some createdAt',
            'requestedSupplierAction' => 'some requestedSupplierAction',
            'requestedCreditAmount' => 15,
            'actualCreditAmount' => 1,
            'supplierName' => 'some supplierName',
            'supplierNumber' => 'some supplierNumber',
            'supplierErp' => 316,
            'parts' => [[
                '@id' => '/vendorwarrantyclaimparts/1',
                '@type' => 'VendorWarrantyClaimPart',
                'id' => 1,
                'serialNumber' => 'some serialNumber',
                'vendorPartNumber' => 'some vendorPartNumber',
                'vendorSerialNumber' => 'some vendorSerialNumber',
                'failureType' => 'some failure type',
                'failureSystem' => 'some failure system',
                'ship' => true,
                'receivedQuantity' => 12,
                'createdAt' => 'some createdAt',
                'createdBy' => [
                    '@id' => '/persons/1',
                    'username' => 'some username',
                    'email' => 'some email',
                    'firstname' => 'some firstname',
                    'lastname' => 'some lastname',
                ],
                'partNumber' => 'some partNumber',
                'description' => 'some description',
                'quantity' => 13.0,
                'unitOfMeasure' => 'some unitOfMeasure',
                'deletedAt' => 'some deletedAt',
                'deletedBy' => [
                    '@id' => '/persons/2',
                    'username' => 'some username',
                    'email' => 'some email',
                    'firstname' => 'some firstname',
                    'lastname' => 'some lastname',
                ],
            ]],
            'poster' => [
                '@id' => '/persons/2',
                'username' => 'some username',
                'email' => 'some email',
                'firstname' => 'some firstname',
                'lastname' => 'some lastname',
            ],
            'assignee' => [
                '@id' => '/persons/4',
                'username' => 'some username',
                'email' => 'some email',
                'firstname' => 'some firstname',
                'lastname' => 'some lastname',
            ],
            'location' => [
                '@id' => '/location/1',
                '@type' => 'Location',
                'name' => 'some name',
                'erp' => 315,
                'currency' => [
                    '@id' => '/currencies/1',
                    '@type' => 'Currency',
                    'id' => 1,
                    'name' => 'some currency name',
                ],
            ],
            'supplierReturnMerchandiseAuthorization' => 'some supplierReturnMerchandiseAuthorization',
            'supplierShippingInstruction' => 'some supplierShippingInstruction',
            'supplierShipperName' => 'some supplierShipperName',
            'trackingNumber' => 'some trackingNumber',
            'supplierCreditAmount' => 10,
            'supplierCreditNote' => 'some supplierCreditNote',
            'shipBackDefectivePart' => true,
            'scarRequested' => true,
            'accepted' => true,
            'activity' => [[
                '@id' => '/activities/1',
                '@type' => 'Comment',
                'resource' => 'some resource',
                'message' => 'some comment',
                'user' => [
                    '@id' => '/persons/1',
                    'username' => 'some_username',
                    'email' => 'some_email',
                    'firstname' => 'some_firstname',
                    'lastname' => 'some_lastname',
                ],
                'createdAt' => 'some created at',
                'updatedAt' => 'some updated at',
                'public' => false,
                'metadata' => [],
                'files' => [],
            ]],
            'vendorToRespondAt' => '1990-08-11',
        ]];

        // #1 with nullable values (*)
        yield [[
            '@id' => '/vendorwarrantyclaims/1',
            '@type' => 'NcrVendorWarrantyClaim',
            'nonConformity' => [
                '@id' => '/nonconformities/1',
                '@type' => 'NonConformity',
                'id' => 1,
                'location' => [
                    '@id' => '/location/1',
                    '@type' => 'Location',
                    'name' => 'some name',
                    'erp' => 315,
                    'currency' => [
                        '@id' => '/currencies/1',
                        '@type' => 'Currency',
                        'id' => 1,
                        'name' => 'some currency name',
                    ],
                ],
                'process' => null, // (*)
                'createdAt' => 'some createdAt',
                'status' => 'some status',
                'reportedBy' => null, // (*)
                'hours' => null, // (*),
                'supplier' => 'some supplier',
                'problem' => 'some problem',
                'shortDescription' => 'some shortDescription',
                'solution' => null, // (*)
                'responsible' => null, // (*),
                'purchaseOrderNumber' => null, // (*)
                'rush' => true,
                'chargeVendor' => true,
                'failureType' => null, // (*)
                'iFactor' => 'some iFactor',
                'investigation' => null, // (*)
                'scrap' => true,
                'rework' => true,
                'firstArticleInspection' => true,
                'useAsIs' => true,
                'derogation' => true,
                'returnVendor' => true,
                'chargeVendorForRepair' => true,
                'supplierCorrectiveActionRequest' => true,
                'internalCorrectiveActionRequest' => true,
                'other' => true,
                'containment' => true,
                'environmentalIssue' => true,
                'actionComment' => null, // (*),
                'currency' => null, // (*)
                'cost' => null, // (*)
                'costBreakdown' => null, // (*)
                'nonQualityCost' => null, // (*)
                'supplierName' => null, // (*)
                'supplierNumber' => null, // (*)
                'supplierErp' => null, // (*)
                'invoiceNumber' => null, // (*)
                'mainFile' => null, // (*)
                'parts' => [[
                    '@id' => '/NonConformityPart/1',
                    '@type' => 'NonConformityPart',
                    'reference' => null, // (*)
                    'referenceNumber' => null, // (*)
                    'serialNumber' => null, // (*)
                    'createdAt' => 'some createdAt',
                    'createdBy' => null, // (*),
                    'partNumber' => 'some partNumber',
                    'description' => 'some description',
                    'quantity' => 1,
                    'unitOfMeasure' => 'some unitOfMeasure',
                    'deletedAt' => null, // (*)
                    'deletedBy' => null, // (*)
                    'id' => 1,
                ]],
            ],
            'type' => [
                '@id' => '/vendorwarrantyclaimtypes/1',
                '@type' => 'VendorWarrantyClaimType',
                'name' => 'some name',
                'description' => 'some description',
            ],
            'id' => 1,
            'status' => 'PENDING',
            'statusUpdatedAt' => null, // (*)
            'createdAt' => 'some createdAt',
            'requestedSupplierAction' => 'some requestedSupplierAction',
            'requestedCreditAmount' => 16,
            'actualCreditAmount' => null, // (*)
            'supplierName' => null, // (*)
            'supplierNumber' => null, // (*)
            'supplierErp' => null, // (*)
            'parts' => [[
                '@id' => '/vendorwarrantyclaimparts/1',
                '@type' => 'VendorWarrantyClaimPart',
                'id' => 1,
                'serialNumber' => 'some serialNumber',
                'vendorPartNumber' => 'some vendorPartNumber',
                'vendorSerialNumber' => 'some vendorSerialNumber',
                'failureType' => 'some failure type',
                'failureSystem' => 'some failure system',
                'ship' => true,
                'receivedQuantity' => 12,
                'createdAt' => 'some createdAt',
                'createdBy' => [
                    '@id' => '/persons/1',
                    'username' => 'some username',
                    'email' => 'some email',
                    'firstname' => 'some firstname',
                    'lastname' => 'some lastname',
                ],
                'partNumber' => 'some partNumber',
                'description' => 'some description',
                'quantity' => 13.0,
                'unitOfMeasure' => 'some unitOfMeasure',
                'deletedAt' => 'some deletedAt',
                'deletedBy' => [
                    '@id' => '/persons/2',
                    'username' => 'some username',
                    'email' => 'some email',
                    'firstname' => 'some firstname',
                    'lastname' => 'some lastname',
                ],
            ]],
            'poster' => null, // (*)
            'assignee' => null, // (*)
            'location' => null, // (*)
            'supplierReturnMerchandiseAuthorization' => null, // (*)
            'supplierShippingInstruction' => null, // (*)
            'supplierShipperName' => null, // (*)
            'trackingNumber' => null, // (*)
            'supplierCreditAmount' => null, // (*)
            'supplierCreditNote' => null, // (*)
            'shipBackDefectivePart' => null, // (*)
            'scarRequested' => true,
            'accepted' => null, // (*),
            'activity' => [[
                '@id' => '/activities/1',
                '@type' => 'Comment',
                'resource' => 'some resource',
                'message' => 'some comment',
                'user' => null, // (*),
                'createdAt' => 'some created at',
                'updatedAt' => 'some updated at',
                'public' => false,
                'metadata' => [],
                'files' => [],
            ]],
            'vendorToRespondAt' => null,
        ]];

        // #2 with optional fields removed (//)
        yield [[
            '@id' => '/vendorwarrantyclaims/1',
            '@type' => 'NcrVendorWarrantyClaim',
            'nonConformity' => [
                '@id' => '/nonconformities/1',
                '@type' => 'NonConformity',
                'id' => 1,
                // 'factory'
                // 'process'
                // 'createdAt'
                // 'status'
                // 'reportedBy'
                // 'hours'
                // 'supplier'
                // 'problem'
                // 'shortDescription'
                // 'solution'
                // 'responsible'
                // 'purchaseOrderNumber'
                // 'rush'
                // 'chargeVendor'
                // 'failureType'
                // 'iFactor'
                // 'investigation'
                // 'scrap'
                // 'rework'
                // 'firstArticleInspection'
                // 'useAsIs'
                // 'derogation'
                // 'returnVendor'
                // 'chargeVendorForRepair'
                // 'supplierCorrectiveActionRequest'
                // 'internalCorrectiveActionRequest'
                // 'other'
                // 'containment'
                // 'environmentalIssue'
                // 'actionComment'
                // 'currency'
                // 'cost'
                // 'costBreakdown'
                // 'nonQualityCost'
                // 'supplierName'
                // 'supplierNumber'
                // 'supplierErp'
                // 'invoiceNumber'
                // 'mainFile'
                // 'parts'
            ],
            // 'type'
            'id' => 1,
            'status' => 'PENDING',
            'statusUpdatedAt' => 'some statusUpdatedAt',
            'createdAt' => 'some createdAt',
            'requestedSupplierAction' => 'some requestedSupplierAction',
            'requestedCreditAmount' => 15,
            'actualCreditAmount' => 1,
            'supplierName' => 'some supplierName',
            'supplierNumber' => 'some supplierNumber',
            'supplierErp' => 316,
            'parts' => [],
            // 'poster' =>
            'assignee' => null, // @fixme not optional
            // 'factory'
            // 'supplierReturnMerchandiseAuthorization'
            // 'supplierShippingInstruction'
            // 'supplierShipperName'
            // 'trackingNumber'
            // 'supplierCreditAmount'
            // 'supplierCreditNote'
            // 'shipBackDefectivePart'
            // 'scarRequested'
            // 'accepted'
            // 'activity'
        ]];

        // #3 with empty collections (*)
        yield [[
            '@id' => '/vendorwarrantyclaims/1',
            '@type' => 'NcrVendorWarrantyClaim',
            'nonConformity' => [
                '@id' => '/nonconformities/1',
                '@type' => 'NonConformity',
                'id' => 1,
                'parts' => [], // (*)
            ],
            'id' => 1,
            'status' => 'PENDING',
            'statusUpdatedAt' => 'some statusUpdatedAt',
            'createdAt' => 'some createdAt',
            'requestedSupplierAction' => 'some requestedSupplierAction',
            'requestedCreditAmount' => 15,
            'actualCreditAmount' => 1,
            'supplierName' => 'some supplierName',
            'supplierNumber' => 'some supplierNumber',
            'supplierErp' => 316,
            'parts' => [],
            'assignee' => null,
            'activity' => [],  // (*)
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        // #0 no data
        yield [[]];

        // #1 unknwon field only
        yield [[
            'unknwon_field' => 'qux',
        ]];

        // #2 not allowed nullable field (*)
        yield [[
            '@id' => '/vendorwarrantyclaims/1',
            '@type' => null, // (*)
            'nonConformity' => [
                '@id' => '/nonconformities/1',
                '@type' => 'NonConformity',
                'id' => null, // (*)
            ],
            'id' => 1,
            'status' => 'PENDING',
            'statusUpdatedAt' => 'some statusUpdatedAt',
            'createdAt' => 'some createdAt',
            'requestedSupplierAction' => 'some requestedSupplierAction',
            'requestedCreditAmount' => 15,
            'actualCreditAmount' => 1,
            'supplierName' => 'some supplierName',
            'supplierNumber' => 'some supplierNumber',
            'supplierErp' => 316,
            'parts' => [],
            'assignee' => null,
        ]];

        // #3 incorrect types (*)
        yield [[
            '@id' => '/vendorwarrantyclaims/1',
            '@type' => 'NcrVendorWarrantyClaim',
            'nonConformity' => [
                '@id' => '/nonconformities/1',
                '@type' => 'NonConformity',
                'id' => '1', // (*)
            ],
            'id' => 1,
            'status' => 'PENDING',
            'statusUpdatedAt' => 'some statusUpdatedAt',
            'createdAt' => 'some createdAt',
            'requestedSupplierAction' => 'some requestedSupplierAction',
            'requestedCreditAmount' => '15', // (*)
            'actualCreditAmount' => '1', // (*)
            'supplierName' => 'some supplierName',
            'supplierNumber' => 'some supplierNumber',
            'supplierErp' => '316', // (*)
            'parts' => [],
            'assignee' => null,
        ]];
    }

    protected function getResourceClass(): string
    {
        return VendorWarrantyClaimInterface::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new VendorWarrantyClaimResourceTransformer();
    }

    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(NCRVendorWarrantyClaim::class, $resource);
        self::assertSame($structure['@id'], $resource->iri);

        if (isset($structure['type'])) {
            self::assertSame($structure['type']['@id'], $resource->type->iri);
            self::assertSame($structure['type']['name'], $resource->type->name);
            self::assertSame($structure['type']['description'], $resource->type->description);
        }

        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['status'], $resource->status->value);

        self::assertSame($structure['statusUpdatedAt'], $resource->statusUpdatedAt);
        self::assertSame($structure['createdAt'], $resource->createdAt);
        self::assertSame($structure['requestedSupplierAction'], $resource->requestedSupplierAction);
        self::assertSame($structure['requestedCreditAmount'], $resource->requestedCreditAmount);
        self::assertSame($structure['actualCreditAmount'], $resource->actualCreditAmount);
        self::assertSame($structure['supplierName'], $resource->supplierName);
        self::assertSame($structure['supplierNumber'], $resource->supplierNumber);
        self::assertSame($structure['supplierErp'], $resource->supplierErp);

        if (($structure['nonConformity']['mainFile'] ?? null) !== null) {
            self::assertSame($structure['nonConformity']['mainFile']['@id'], $resource->nonConformity->mainFile->iri);
            self::assertSame($structure['nonConformity']['mainFile']['id'], $resource->nonConformity->mainFile->id);
            self::assertSame($structure['nonConformity']['mainFile']['filePath'], $resource->nonConformity->mainFile->filePath);
            self::assertSame($structure['nonConformity']['mainFile']['createdAt'], $resource->nonConformity->mainFile->createdAt);
            self::assertSame($structure['nonConformity']['mainFile']['description'], $resource->nonConformity->mainFile->description);
            self::assertSame($structure['nonConformity']['mainFile']['sha'], $resource->nonConformity->mainFile->sha);
            self::assertSame($structure['nonConformity']['mainFile']['mimeType'], $resource->nonConformity->mainFile->mimeType);
            self::assertSame($structure['nonConformity']['mainFile']['extension'], $resource->nonConformity->mainFile->extension);
            self::assertSame($structure['nonConformity']['mainFile']['size'], $resource->nonConformity->mainFile->size);

            self::assertSame($structure['nonConformity']['mainFile']['poster']['@id'], $resource->nonConformity->mainFile->poster->iri);
            self::assertSame($structure['nonConformity']['mainFile']['poster']['username'], $resource->nonConformity->mainFile->poster->username);
            self::assertSame($structure['nonConformity']['mainFile']['poster']['email'], $resource->nonConformity->mainFile->poster->email);
            self::assertSame($structure['nonConformity']['mainFile']['poster']['firstname'], $resource->nonConformity->mainFile->poster->firstname);
            self::assertSame($structure['nonConformity']['mainFile']['poster']['lastname'], $resource->nonConformity->mainFile->poster->lastname);
        }

        if (count($structure['parts'] ?? []) > 0) {
            self::assertSame($structure['parts'][0]['@id'], $resource->parts[0]->iri);
            self::assertSame($structure['parts'][0]['id'], $resource->parts[0]->id);
            self::assertSame($structure['parts'][0]['serialNumber'], $resource->parts[0]->serialNumber);
            self::assertSame($structure['parts'][0]['vendorPartNumber'], $resource->parts[0]->vendorPartNumber);
            self::assertSame($structure['parts'][0]['vendorSerialNumber'], $resource->parts[0]->vendorSerialNumber);
            self::assertSame($structure['parts'][0]['failureType'], $resource->parts[0]->failureType);
            self::assertSame($structure['parts'][0]['failureSystem'], $resource->parts[0]->failureSystem);
            self::assertSame($structure['parts'][0]['ship'], $resource->parts[0]->ship);
            self::assertSame($structure['parts'][0]['receivedQuantity'], $resource->parts[0]->receivedQuantity);
            self::assertSame($structure['parts'][0]['createdAt'], $resource->parts[0]->createdAt);

            self::assertSame($structure['parts'][0]['createdBy']['@id'], $resource->parts[0]->createdBy->iri);
            self::assertSame($structure['parts'][0]['createdBy']['username'], $resource->parts[0]->createdBy->username);
            self::assertSame($structure['parts'][0]['createdBy']['email'], $resource->parts[0]->createdBy->email);
            self::assertSame($structure['parts'][0]['createdBy']['firstname'], $resource->parts[0]->createdBy->firstname);
            self::assertSame($structure['parts'][0]['createdBy']['lastname'], $resource->parts[0]->createdBy->lastname);

            self::assertSame($structure['parts'][0]['partNumber'], $resource->parts[0]->partNumber);
            self::assertSame($structure['parts'][0]['description'], $resource->parts[0]->description);
            self::assertSame($structure['parts'][0]['quantity'], $resource->parts[0]->quantity);
            self::assertSame($structure['parts'][0]['unitOfMeasure'], $resource->parts[0]->unitOfMeasure);
            self::assertSame($structure['parts'][0]['deletedAt'], $resource->parts[0]->deletedAt);

            if (null !== $structure['parts'][0]['deletedBy']) {
                self::assertSame($structure['parts'][0]['deletedBy']['@id'], $resource->parts[0]->deletedBy->iri);
                self::assertSame($structure['parts'][0]['deletedBy']['username'], $resource->parts[0]->deletedBy->username);
                self::assertSame($structure['parts'][0]['deletedBy']['email'], $resource->parts[0]->deletedBy->email);
                self::assertSame($structure['parts'][0]['deletedBy']['firstname'], $resource->parts[0]->deletedBy->firstname);
                self::assertSame($structure['parts'][0]['deletedBy']['lastname'], $resource->parts[0]->deletedBy->lastname);
            }
        }

        if (isset($structure['poster'])) {
            self::assertSame($structure['poster']['@id'], $resource->poster->iri);
            self::assertSame($structure['poster']['username'], $resource->poster->username);
            self::assertSame($structure['poster']['email'], $resource->poster->email);
            self::assertSame($structure['poster']['firstname'], $resource->poster->firstname);
            self::assertSame($structure['poster']['lastname'], $resource->poster->lastname);
        }

        if (null !== $structure['assignee']) {
            self::assertSame($structure['assignee']['@id'], $resource->assignee->iri);
            self::assertSame($structure['assignee']['username'], $resource->assignee->username);
            self::assertSame($structure['assignee']['email'], $resource->assignee->email);
            self::assertSame($structure['assignee']['firstname'], $resource->assignee->firstname);
            self::assertSame($structure['assignee']['lastname'], $resource->assignee->lastname);
        }

        if (isset($structure['location'])) {
            self::assertSame($structure['location']['@id'], $resource->location->iri);
            self::assertSame($structure['location']['name'], $resource->location->name);
            self::assertSame($structure['location']['erp'], $resource->location->erp);
        }

        if (isset($structure['supplierReturnMerchandiseAuthorization'])) {
            self::assertSame($structure['supplierReturnMerchandiseAuthorization'], $resource->supplierReturnMerchandiseAuthorization);
        }

        if (isset($structure['supplierShippingInstruction'])) {
            self::assertSame($structure['supplierShippingInstruction'], $resource->supplierShippingInstruction);
        }

        if (isset($structure['supplierShipperName'])) {
            self::assertSame($structure['supplierShipperName'], $resource->supplierShipperName);
        }

        if (isset($structure['trackingNumber'])) {
            self::assertSame($structure['trackingNumber'], $resource->trackingNumber);
        }

        if (isset($structure['supplierCreditAmount'])) {
            self::assertSame($structure['supplierCreditAmount'], $resource->supplierCreditAmount);
        }

        if (isset($structure['supplierCreditNote'])) {
            self::assertSame($structure['supplierCreditNote'], $resource->supplierCreditNote);
        }

        if (isset($structure['shipBackDefectivePart'])) {
            self::assertSame($structure['shipBackDefectivePart'], $resource->shipBackDefectivePart);
        }

        if (isset($structure['scarRequested'])) {
            self::assertSame($structure['scarRequested'], $resource->scarRequested);
        }

        if (isset($structure['accepted'])) {
            self::assertSame($structure['accepted'], $resource->accepted);
        }

        if (count($structure['activity'] ?? []) > 0) {
            self::assertSame($structure['activity'][0]['@id'], $resource->activities[0]->iri);
            self::assertSame($structure['activity'][0]['resource'], $resource->activities[0]->resource);
            self::assertSame($structure['activity'][0]['message'], $resource->activities[0]->message);
            self::assertSame($structure['activity'][0]['createdAt'], $resource->activities[0]->createdAt);
            self::assertSame($structure['activity'][0]['updatedAt'], $resource->activities[0]->updatedAt);
            self::assertSame($structure['activity'][0]['public'], $resource->activities[0]->public);
            self::assertSame($structure['activity'][0]['metadata'], $resource->activities[0]->metadata);
            self::assertSame($structure['activity'][0]['files'], $resource->activities[0]->files);

            if (null !== $structure['activity'][0]['user']) {
                self::assertSame($structure['activity'][0]['user']['@id'], $resource->activities[0]->user->iri);
                self::assertSame($structure['activity'][0]['user']['username'], $resource->activities[0]->user->username);
                self::assertSame($structure['activity'][0]['user']['email'], $resource->activities[0]->user->email);
                self::assertSame($structure['activity'][0]['user']['firstname'], $resource->activities[0]->user->firstname);
                self::assertSame($structure['activity'][0]['user']['lastname'], $resource->activities[0]->user->lastname);
            }
        }

        self::assertSame($structure['vendorToRespondAt'] ?? null, $resource->vendorToRespondAt);
    }

    protected function supportsCollections(): bool
    {
        return true;
    }

    protected function supportsPage(): bool
    {
        return false;
    }
}
