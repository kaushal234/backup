<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Resource\Amount;
use App\Sdk\Resource\Comment;
use App\Sdk\Resource\Person;
use App\Sdk\Resource\PurchaseOrder;
use App\Sdk\Resource\PurchaseOrderLine;
use App\Sdk\Resource\TextItem;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Str;
use Psl\Type;
use Psl\Vec;

use function array_key_exists;

/**
 * @implements ResourceTransformerInterface<PurchaseOrder>
 */
final class PurchaseOrderResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        if (PurchaseOrder::class !== $resource) {
            return false;
        }

        return PurchaseOrder::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): PurchaseOrder
    {
        try {
            $structure = PurchaseOrder::getTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a purchase order structure.', previous: $e);
        }

        return new PurchaseOrder(
            iri: $structure['@id'],
            id: $structure['orderIdentifier'],
            erp: (int) mb_substr($structure['purchaseOfficeCode'], 0, 3),
            supplierNumber: $structure['buyFromSupplierCode'] ?? null,
            referenceA: $structure['reference1'],
            referenceB: $structure['reference2'],
            orderDate: $structure['orderDatetime'],
            deliveryDate: $structure['plannedReceiptDate'],
            confirmedDeliveryDate: $structure['orderDatetime'],
            lines: Vec\map($structure['lines'] ?? [], static fn ($line) => new PurchaseOrderLine(
                iri: $line['@id'],
                id: null,
                lineIdentifier: $line['lineIdentifier'],
                sequence: $line['sequence'],
                purchaseOrderIdentifier: $structure['orderIdentifier'],
                partNumber: $line['itemCode'],
                supplierNumber: $structure['buyFromSupplierCode'] ?? null,
                erp: (int) mb_substr($structure['purchaseOfficeCode'], 0, 3),
                quantity: $line['quantity']['value'],
                deliveredQuantity: $line['receivedQuantity']['value'] ?? 0,
                plannedDeliveryDate: $line['plannedReceiptDate'],
                confirmedSupplierDate: $line['confirmedSupplierDate'] ?? null,
                rescheduledDeliveryDate: $line['rescheduledDate'] ?? null,
                shippingDate: $line['shippingDate'] ?? null,
                signalCode: null,
                leadTime: null,
                price: array_key_exists('price', $line) ? new Amount(
                    value: $line['price']['value'],
                    currency: $line['price']['currency'],
                    unitOfMeasure: $line['price']['unitOfMeasure'],
                ) : null,
                revision: $line['engineeringItemRevision'],
                description: $line['description'],
                blocked: null,
                late: $line['late'],
                unconfirmed: $line['unconfirmed'],
                toBeDeliveredIn7Days: $line['toBeDeliveredWithin7Days'],
                isConfirmable: $line['isConfirmable'],
                isToCancel: $line['isToCancel'] ?? false,
                canceled: $line['canceled'] ?? null,
                lineState: $line['lineState'],
                expired: $line['expired'],
                site: $line['site'],
                supplierItemCode: $line['supplierItemCode'] ?? null,
                project: $line['project'],
                revisionEffectiveDate: $line['engineeringRevisionEffectiveDate'],
                revisionExpiryDate: $line['engineeringRevisionExpiryDate'],
                lineTexts: Vec\map($line['lineTexts']['textByLanguages'] ?? [], static fn ($text) => new TextItem(
                    iri: $text['@id'],
                    type: $text['@type'],
                    langCode: $text['lang'],
                    lang: $text['name'],
                    texts: $text['text']
                )),
            )),
            comments: Vec\map($structure['activity'] ?? [], static fn ($comment) => new Comment(
                iri: $comment['@id'],
                resource: $comment['resource'],
                message: $comment['message'],
                user: $comment['user'] ? new Person(
                    iri: $comment['user']['@id'],
                    username: $comment['user']['username'],
                    email: $comment['user']['email'],
                    firstname: $comment['user']['firstname'],
                    lastname: $comment['user']['lastname'],
                ) : null,
                createdAt: $comment['createdAt'],
                updatedAt: $comment['updatedAt'],
                public: $comment['public'],
            )),
            status: Vec\map($structure['status'] ?? [], Str\lowercase(...)),
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        if (PurchaseOrder::class !== $resource) {
            return false;
        }

        return PurchaseOrder::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = PurchaseOrder::getCollectionTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of purchase order structures.', previous: $e);
        }

        return Vector::fromArray($collection['hydra:member'])->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): never
    {
        throw new FailedTransformationException('Pagination is not supported for PO resource.');
    }
}
