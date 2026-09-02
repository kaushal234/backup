<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\DataTransformer\ResourceTransformer\VendorWarrantyClaimResourceTransformer;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\VendorWarrantyClaimInterface;
use App\Sdk\Resource\WCVendorWarrantyClaim;

use function count;

/**
 * @extends AbstractResourceTransformerTest<WCVendorWarrantyClaim>
 *
 * @group unit
 */
final class WCVendorWarrantyClaimResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        // #0 with all data
        yield [[
            '@id' => '/vendorwarrantyclaims/1',
            '@type' => 'WcVendorWarrantyClaim',
            'id' => 1,
            'status' => 'PENDING', // can be a array too
            'type' => [
                '@id' => '/vendorwarrantyclaimtypes/1',
                '@type' => 'VendorWarrantyClaimType',
                'name' => 'some name',
                'description' => 'some description',
            ],
            'warrantyClaimId' => 1,
            'requestedSupplierAction' => 'some requestedSupplierAction',
            'statusUpdatedAt' => 'some statusUpdatedAt',
            'createdAt' => 'some createdAt',
            'requestedCreditAmount' => 10,
            'actualCreditAmount' => 11,
            'supplierName' => 'some supplierName',
            'supplierNumber' => 'some supplierNumber',
            'supplierErp' => 314,
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
            'factory' => [
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
            'supplierCreditAmount' => 14,
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
            '@type' => 'WcVendorWarrantyClaim',
            'id' => 1,
            'status' => 'PENDING', // can be a array too
            'type' => [
                '@id' => '/vendorwarrantyclaimtypes/1',
                '@type' => 'VendorWarrantyClaimType',
                'name' => 'some name',
                'description' => 'some description',
            ],
            'warrantyClaimId' => 1,
            'requestedSupplierAction' => 'some requestedSupplierAction',
            'statusUpdatedAt' => null, // (*)
            'createdAt' => 'some createdAt',
            'requestedCreditAmount' => 10,
            'actualCreditAmount' => null, // (*)
            'supplierName' => null, // (*)
            'supplierNumber' => null, // (*)
            'supplierErp' => null, // (*)
            'parts' => [[
                '@id' => '/vendorwarrantyclaimparts/1',
                '@type' => 'VendorWarrantyClaimPart',
                'id' => 1,
                'serialNumber' => null, // (*)
                'vendorPartNumber' => null, // (*)
                'vendorSerialNumber' => null, // (*)
                'failureType' => null, // (*)
                'failureSystem' => null, // (*)
                'ship' => true,
                'receivedQuantity' => null, // (*)
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
                'deletedAt' => null, // (*)
                'deletedBy' => null, // (*),
            ]],
            'poster' => null, // (*),
            'assignee' => null, // (*),
            'factory' => null, // (*),
            'supplierReturnMerchandiseAuthorization' => null, // (*)
            'supplierShippingInstruction' => null, // (*)
            'supplierShipperName' => null, // (*)
            'trackingNumber' => null, // (*)
            'supplierCreditAmount' => null, // (*)
            'supplierCreditNote' => null, // (*)
            'shipBackDefectivePart' => false,
            'scarRequested' => false,
            'accepted' => false,
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
            '@id' => '/vendorwarrantyclaims/3',
            '@type' => 'WcVendorWarrantyClaim',
            'id' => 1,
            'status' => 'PENDING',
            // 'type' => ,
            'warrantyClaimId' => 1,
            'requestedSupplierAction' => 'some requestedSupplierAction',
            'statusUpdatedAt' => null,
            'createdAt' => 'some createdAt',
            'requestedCreditAmount' => 10,
            'actualCreditAmount' => null,
            'supplierName' => null,
            'supplierNumber' => null,
            'supplierErp' => null,
            'parts' => [],
            'poster' => null,
            'assignee' => null,
            // 'factory' => null,
            'supplierReturnMerchandiseAuthorization' => null,
            'supplierShippingInstruction' => null,
            'supplierShipperName' => null,
            'trackingNumber' => null,
            'supplierCreditAmount' => null,
            'supplierCreditNote' => null,
            'shipBackDefectivePart' => true,
            'scarRequested' => true,
            'accepted' => true,
            // 'activity' => [],
        ]];

        // #3 with empty collections (*)
        yield [[
            '@id' => '/vendorwarrantyclaims/3',
            '@type' => 'WcVendorWarrantyClaim',
            'id' => 1,
            'status' => 'PENDING',
            'type' => [
                '@id' => '/vendorwarrantyclaimtypes/3',
                '@type' => 'VendorWarrantyClaimType',
                'name' => 'some name',
                'description' => 'some description',
            ],
            'warrantyClaimId' => 1,
            'requestedSupplierAction' => 'some requestedSupplierAction',
            'statusUpdatedAt' => null,
            'createdAt' => 'some createdAt',
            'requestedCreditAmount' => 10,
            'actualCreditAmount' => null,
            'supplierName' => null,
            'supplierNumber' => null,
            'supplierErp' => null,
            'parts' => [], // (*),
            'poster' => null,
            'assignee' => null,
            'factory' => null,
            'supplierReturnMerchandiseAuthorization' => null,
            'supplierShippingInstruction' => null,
            'supplierShipperName' => null,
            'trackingNumber' => null,
            'supplierCreditAmount' => null,
            'supplierCreditNote' => null,
            'shipBackDefectivePart' => true,
            'scarRequested' => true,
            'accepted' => true,
            'activity' => [],  // (*),
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
            '@id' => '/vendorwarrantyclaims/3',
            '@type' => 'WcVendorWarrantyClaim',
            'id' => 1,
            'status' => null, // (*)
            'type' => [
                '@id' => '/vendorwarrantyclaimtypes/3',
                '@type' => 'VendorWarrantyClaimType',
                'name' => 'some name',
                'description' => 'some description',
            ],
            'warrantyClaimId' => 1,
            'requestedSupplierAction' => 'some requestedSupplierAction',
            'statusUpdatedAt' => null,
            'createdAt' => 'some createdAt',
            'requestedCreditAmount' => 10,
            'actualCreditAmount' => null,
            'supplierName' => null,
            'supplierNumber' => null,
            'supplierErp' => null,
            'parts' => [],
            'poster' => null,
            'assignee' => null,
            'factory' => null,
            'supplierReturnMerchandiseAuthorization' => null,
            'supplierShippingInstruction' => null,
            'supplierShipperName' => null,
            'trackingNumber' => null,
            'supplierCreditAmount' => null,
            'supplierCreditNote' => null,
            'shipBackDefectivePart' => true,
            'scarRequested' => true,
            'accepted' => true,
            'activity' => [],
        ]];

        // #3 incorrect types (*)
        yield [[
            '@id' => '/vendorwarrantyclaims/3',
            '@type' => 'WcVendorWarrantyClaim',
            'id' => '1', // (*)
            'status' => 'PENDING',
            'type' => [
                '@id' => '/vendorwarrantyclaimtypes/3',
                '@type' => 'VendorWarrantyClaimType',
                'name' => 'some name',
                'description' => 'some description',
            ],
            'warrantyClaimId' => '1', // (*)
            'requestedSupplierAction' => 'some requestedSupplierAction',
            'statusUpdatedAt' => null,
            'createdAt' => 'some createdAt',
            'requestedCreditAmount' => '10', // (*)
            'actualCreditAmount' => null,
            'supplierName' => null,
            'supplierNumber' => null,
            'supplierErp' => null,
            'parts' => [],
            'poster' => null,
            'assignee' => null,
            'factory' => null,
            'supplierReturnMerchandiseAuthorization' => null,
            'supplierShippingInstruction' => null,
            'supplierShipperName' => null,
            'trackingNumber' => null,
            'supplierCreditAmount' => null,
            'supplierCreditNote' => null,
            'shipBackDefectivePart' => true,
            'scarRequested' => true,
            'accepted' => true,
            'activity' => 1, // (*)
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
        self::assertInstanceOf(WCVendorWarrantyClaim::class, $resource);
        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['status'], $resource->status->value);

        if (isset($structure['type'])) {
            self::assertSame($structure['type']['@id'], $resource->type->iri);
            self::assertSame($structure['type']['name'], $resource->type->name);
            self::assertSame($structure['type']['description'], $resource->type->description);
        }

        self::assertSame($structure['warrantyClaimId'], $resource->warrantyClaimId);
        self::assertSame($structure['requestedSupplierAction'], $resource->requestedSupplierAction);
        self::assertSame($structure['statusUpdatedAt'], $resource->statusUpdatedAt);
        self::assertSame($structure['createdAt'], $resource->createdAt);
        self::assertSame($structure['requestedCreditAmount'], $resource->requestedCreditAmount);
        self::assertSame($structure['actualCreditAmount'], $resource->actualCreditAmount);
        self::assertSame($structure['supplierName'], $resource->supplierName);
        self::assertSame($structure['supplierNumber'], $resource->supplierNumber);
        self::assertSame($structure['supplierErp'], $resource->supplierErp);

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

        if (null !== $structure['poster']) {
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

        self::assertSame($structure['supplierReturnMerchandiseAuthorization'], $resource->supplierReturnMerchandiseAuthorization);
        self::assertSame($structure['supplierShippingInstruction'], $resource->supplierShippingInstruction);
        self::assertSame($structure['supplierShipperName'], $resource->supplierShipperName);
        self::assertSame($structure['trackingNumber'], $resource->trackingNumber);
        self::assertSame($structure['supplierCreditAmount'], $resource->supplierCreditAmount);
        self::assertSame($structure['supplierCreditNote'], $resource->supplierCreditNote);
        self::assertSame($structure['shipBackDefectivePart'], $resource->shipBackDefectivePart);
        self::assertSame($structure['scarRequested'], $resource->scarRequested);
        self::assertSame($structure['accepted'], $resource->accepted);

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
