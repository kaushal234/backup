<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\PurchaseOrderResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\PurchaseOrder;
use App\Sdk\Resource\ResourceInterface;

use function array_key_exists;
use function count;

/**
 * @extends AbstractResourceTransformerTest<PurchaseOrder>
 *
 * @group unit
 */
final class PurchaseOrderResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield 'with all data' => [[
            '@id' => '/ion/purchase_orders/S1',
            '@type' => 'PurchaseOrder',
            'orderIdentifier' => 'S1',
            'purchaseOfficeCode' => '300code',
            'orderTypeCode' => 'code 1',
            'buyFromSupplierCode' => 'supplier code',
            'orderCurrency' => 'EUR',
            'orderDatetime' => '2021-09-16T09:38:00+00:00',
            'plannedReceiptDate' => '2021-09-16T09:38:00+00:00',
            'reference1' => '',
            'reference2' => '',
            'lines' => [[
                '@type' => 'PurchaseOrderLine',
                '@id' => '_:3094',
                'lineIdentifier' => '10',
                'sequence' => 1,
                'itemCode' => 'item code',
                'supplierItemCode' => null,
                'engineeringItemRevision' => 'A',
                'projectCode' => '',
                'shipToAddress' => [
                    '@type' => 'PurchaseOrderLineAddress',
                    '@id' => '_:2936',
                    'name' => 'address',
                    'addressLine1' => '',
                    'addressLine2' => '',
                    'addressLine3' => '',
                    'addressLine4' => '',
                    'addressLine5' => '',
                    'addressLine6' => '',
                    'postalCode' => '',
                    'cityCode' => 'city',
                    'cityDescription' => 'address description',
                    'stateOrProvinceCode' => '',
                    'countryCode' => 'space',
                    'telephone' => '',
                    'fax' => '',
                    'telex' => '',
                    'internetURL' => '',
                    'emailAddress' => '',
                    'timeZone' => 'EST',
                ],
                'warehouseCode' => '300WC',
                'orderDatetime' => '2021-09-16T09:38:00+00:00',
                'plannedReceiptDate' => '2021-09-16T09:38:00+00:00',
                'rescheduledDate' => '2021-09-16T09:38:00+00:00',
                'shippingDate' => '2021-09-16T09:38:00+00:00',
                'confirmedSupplierDate' => null,
                'receivedQuantity' => [
                    '@type' => 'Quantity',
                    '@id' => '_:3264',
                    'value' => 1,
                    'unitOfMeasure' => 'EA',
                ],
                'quantity' => [
                    '@type' => 'Quantity',
                    '@id' => '_:3517',
                    'value' => 1,
                    'unitOfMeasure' => 'EA',
                ],
                'backOrderQuantity' => [
                    '@type' => 'Quantity',
                    '@id' => '_:3511',
                    'value' => 0,
                    'unitOfMeasure' => '',
                ],
                'price' => [
                    '@type' => 'Amount',
                    '@id' => '_:3562',
                    'value' => 30000,
                    'currency' => 'EUR',
                    'unitOfMeasure' => 'EA',
                ],
                'discount' => [
                    '@type' => 'Amount',
                    '@id' => '_:3569',
                    'value' => 0,
                    'currency' => 'EUR',
                    'unitOfMeasure' => 'EA',
                ],
                'lineTexts' => [],
                'description' => '',
                'late' => false,
                'unconfirmed' => true,
                'toBeDeliveredWithin7Days' => false,
                'signalCode' => null,
                'leadTime' => null,
                'blocked' => null,
                'site' => null,
                'project' => null,
                'engineeringRevisionEffectiveDate' => null,
                'engineeringRevisionExpiryDate' => null,
                'expired' => true,
                'canceled' => false,
                'isToCancel' => true,
                'isConfirmable' => true,
                'lineState' => 'to be canceled',
            ]],
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
            'status' => ['some status'],
        ]];

        yield 'with some nullable values' => [[
            '@id' => '/ion/purchase_orders/S1',
            '@type' => 'PurchaseOrder',
            'orderIdentifier' => 'S1',
            'purchaseOfficeCode' => '300',
            'orderTypeCode' => 'code 1',
            'buyFromSupplierCode' => null,
            'orderCurrency' => null,
            'orderDatetime' => null,
            'plannedReceiptDate' => null,
            'reference1' => null,
            'reference2' => null,
            'lines' => [[
                '@type' => 'PurchaseOrderLine',
                '@id' => '_:3094',
                'lineIdentifier' => '10',
                'sequence' => 2,
                'itemCode' => 'item code',
                'supplierItemCode' => null,
                'engineeringItemRevision' => 'null',
                'projectCode' => 'null',
                'shipToAddress' => 'null',
                'warehouseCode' => 'null',
                'orderDatetime' => '2021-09-16T09:38:00+00:00',
                'plannedReceiptDate' => '2021-09-16T09:38:00+00:00',
                'confirmedSupplierDate' => '2021-09-16T09:38:00+00:00',
                'rescheduledDate' => '2021-09-16T09:38:00+00:00',
                'shippingDate' => null,
                'quantity' => [
                    '@type' => 'Quantity',
                    '@id' => '_:3517',
                    'value' => 1,
                    'unitOfMeasure' => 'EA',
                ],
                'receivedQuantity' => [
                    '@type' => 'Quantity',
                    '@id' => '_:3264',
                    'value' => 1,
                    'unitOfMeasure' => 'EA',
                ],
                'backOrderQuantity' => [
                    '@type' => 'Quantity',
                    '@id' => '_:3511',
                    'value' => 0,
                    'unitOfMeasure' => 'EA',
                ],
                'price' => [
                    '@type' => 'Amount',
                    '@id' => '_:3562',
                    'value' => 30000,
                    'currency' => 'EUR',
                    'unitOfMeasure' => 'EA',
                ],
                'lineTexts' => [],
                'description' => '',
                'late' => true,
                'unconfirmed' => true,
                'toBeDeliveredWithin7Days' => true,
                'signalCode' => null,
                'leadTime' => null,
                'blocked' => null,
                'site' => null,
                'project' => null,
                'engineeringRevisionEffectiveDate' => null,
                'engineeringRevisionExpiryDate' => null,
                'expired' => false,
                'isToCancel' => true,
                'canceled' => false,
                'isConfirmable' => true,
                'lineState' => 'to be canceled',
            ]],
            'activity' => [],
            'status' => ['some status'],
        ]];

        yield 'with empty collections' => [[
            '@id' => '/ion/purchase_orders/S1',
            '@type' => 'PurchaseOrder',
            'orderIdentifier' => 'S1',
            'purchaseOfficeCode' => '300',
            'orderTypeCode' => 'code 1',
            'buyFromSupplierCode' => null,
            'orderCurrency' => null,
            'orderDatetime' => null,
            'plannedReceiptDate' => null,
            'reference1' => null,
            'reference2' => null,
            'lines' => [],
            'activity' => [],
            'status' => ['some status'],
        ]];

        yield 'with optional fields removed' => [[
            '@id' => '/ion/purchase_orders/S1',
            '@type' => 'PurchaseOrder',
            'orderIdentifier' => 'S1',
            'purchaseOfficeCode' => '300',
            'orderTypeCode' => 'code 1',
            'orderCurrency' => null,
            'orderDatetime' => null,
            'plannedReceiptDate' => null,
            'reference1' => null,
            'reference2' => null,
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield 'no data provided' => [[]];

        yield 'not allowed nullable field' => [[
            '@id' => null, // (*)
            '@type' => 'PurchaseOrder',
            'orderIdentifier' => null, // (*),
            'purchaseOfficeCode' => '300',
            'orderTypeCode' => 'code 1',
            'buyFromSupplierCode' => null,
            'orderCurrency' => null,
            'orderDatetime' => null,
            'plannedReceiptDate' => null,
            'reference1' => null,
            'reference2' => null,
        ]];

        // incorrect types : 'status' => [1]
        yield 'incorrect types' => [[
            '@id' => '/ion/purchase_orders/S1',
            '@type' => 'PurchaseOrder',
            'orderIdentifier' => 'S1',
            'purchaseOfficeCode' => '300',
            'orderTypeCode' => 'code 1',
            'buyFromSupplierCode' => null,
            'orderCurrency' => null,
            'orderDatetime' => null,
            'plannedReceiptDate' => null,
            'reference1' => null,
            'reference2' => null,
            'status' => [1],
        ]];
    }

    protected function getResourceClass(): string
    {
        return PurchaseOrder::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new PurchaseOrderResourceTransformer();
    }

    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(PurchaseOrder::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame((int) mb_substr($structure['purchaseOfficeCode'], 0, 3), $resource->erp);

        if (array_key_exists('buyFromSupplierCode', $structure)) {
            self::assertSame($structure['buyFromSupplierCode'], $resource->supplierNumber);
        } else {
            self::assertNull($resource->supplierNumber);
        }

        self::assertSame($structure['reference1'], $resource->referenceA);
        self::assertSame($structure['reference2'], $resource->referenceB);
        self::assertSame($structure['orderDatetime'], $resource->orderDate);
        self::assertSame($structure['plannedReceiptDate'], $resource->deliveryDate);
        self::assertSame($structure['orderDatetime'], $resource->confirmedDeliveryDate);

        if (count($structure['lines'] ?? []) > 0) {
            self::assertSame($structure['lines'][0]['@id'], $resource->lines[0]->iri);
            self::assertSame($structure['lines'][0]['lineIdentifier'], $resource->lines[0]->lineIdentifier);
            self::assertSame($structure['lines'][0]['sequence'], $resource->lines[0]->sequence);
            self::assertSame($structure['lines'][0]['itemCode'], $resource->lines[0]->partNumber);
            self::assertSame($structure['lines'][0]['supplierItemCode'], $resource->lines[0]->supplierItemCode);
            self::assertSame($structure['lines'][0]['quantity']['value'], $resource->lines[0]->quantity);
            self::assertSame($structure['lines'][0]['backOrderQuantity']['value'], $resource->lines[0]->getToBeDeliveredQuantity());
            self::assertSame($structure['lines'][0]['quantity']['unitOfMeasure'], $resource->lines[0]->price->unitOfMeasure);
            self::assertSame($structure['lines'][0]['plannedReceiptDate'], $resource->lines[0]->plannedDeliveryDate);
            self::assertSame($structure['lines'][0]['confirmedSupplierDate'], $resource->lines[0]->confirmedSupplierDate);
            self::assertSame($structure['lines'][0]['rescheduledDate'], $resource->lines[0]->rescheduledDeliveryDate);
            self::assertSame($structure['lines'][0]['price']['value'], $resource->lines[0]->price->value);
            self::assertSame($structure['lines'][0]['price']['currency'], $resource->lines[0]->price->currency);
            self::assertSame($structure['lines'][0]['engineeringItemRevision'], $resource->lines[0]->revision);
            self::assertSame($structure['lines'][0]['description'], $resource->lines[0]->description);
            self::assertSame($structure['lines'][0]['signalCode'], $resource->lines[0]->signalCode);
            self::assertSame($structure['lines'][0]['leadTime'], $resource->lines[0]->leadTime);
            self::assertSame($structure['lines'][0]['blocked'], $resource->lines[0]->blocked);
            self::assertSame($structure['lines'][0]['late'], $resource->lines[0]->late);
            self::assertSame($structure['lines'][0]['unconfirmed'], $resource->lines[0]->unconfirmed);
            self::assertSame($structure['lines'][0]['toBeDeliveredWithin7Days'], $resource->lines[0]->toBeDeliveredIn7Days);
            self::assertSame($structure['lines'][0]['lineTexts'], $resource->lines[0]->lineTexts);
            self::assertSame($structure['lines'][0]['site'], $resource->lines[0]->site);
            self::assertSame($structure['lines'][0]['project'], $resource->lines[0]->project);
            self::assertSame($structure['lines'][0]['engineeringRevisionEffectiveDate'], $resource->lines[0]->revisionEffectiveDate);
            self::assertSame($structure['lines'][0]['engineeringRevisionExpiryDate'], $resource->lines[0]->revisionExpiryDate);
            self::assertSame($structure['lines'][0]['expired'], $resource->lines[0]->expired);
            self::assertSame($structure['lines'][0]['canceled'], $resource->lines[0]->canceled);
            self::assertSame($structure['lines'][0]['isConfirmable'], $resource->lines[0]->isConfirmable);
            self::assertSame($structure['lines'][0]['isToCancel'], $resource->lines[0]->isToCancel);
            self::assertSame($structure['lines'][0]['lineState'], $resource->lines[0]->lineState);
        }

        if (count($structure['activity'] ?? []) > 0) {
            self::assertSame($structure['activity'][0]['@id'], $resource->comments[0]->iri);
            self::assertSame($structure['activity'][0]['resource'], $resource->comments[0]->resource);
            self::assertSame($structure['activity'][0]['message'], $resource->comments[0]->message);
            self::assertSame($structure['activity'][0]['createdAt'], $resource->comments[0]->createdAt);
            self::assertSame($structure['activity'][0]['updatedAt'], $resource->comments[0]->updatedAt);
            self::assertSame($structure['activity'][0]['public'], $resource->comments[0]->public);
            self::assertSame($structure['activity'][0]['metadata'], $resource->comments[0]->metadata);
            self::assertSame($structure['activity'][0]['files'], $resource->comments[0]->files);

            self::assertSame($structure['activity'][0]['user']['@id'], $resource->comments[0]->user->iri);
            self::assertSame($structure['activity'][0]['user']['username'], $resource->comments[0]->user->username);
            self::assertSame($structure['activity'][0]['user']['email'], $resource->comments[0]->user->email);
            self::assertSame($structure['activity'][0]['user']['firstname'], $resource->comments[0]->user->firstname);
            self::assertSame($structure['activity'][0]['user']['lastname'], $resource->comments[0]->user->lastname);
        }
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
