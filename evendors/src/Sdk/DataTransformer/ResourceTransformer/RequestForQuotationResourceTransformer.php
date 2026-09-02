<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Resource\RequestForQuotation;
use App\Sdk\Resource\RequestForQuotationLine;
use App\Sdk\Resource\RequestForQuotationStatus;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type;
use Psl\Vec;

/**
 * @implements ResourceTransformerInterface<RequestForQuotation>
 */
final class RequestForQuotationResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        if (RequestForQuotation::class !== $resource) {
            return false;
        }

        return RequestForQuotation::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): RequestForQuotation
    {
        try {
            $structure = RequestForQuotation::getTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into an RFQ structure.', previous: $e);
        }

        $lines = [];
        if (Iter\contains_key($structure, 'lines')) {
            $lines = Vec\map(
                $structure['lines'] ?? [],
                static function ($data): RequestForQuotationLine {
                    $data = RequestForQuotationLine::getTypeStructure()->assert($data);
                    $site = $data['site'] ?? null;

                    return new RequestForQuotationLine(
                        iri: $data['@id'],
                        position: $data['position'],
                        partNumber: $data['item'],
                        description: $data['itemDescription'],
                        date: $data['date'],
                        quantity: $data['quantity'],
                        unitOfMeasure: $data['unitOfMeasure'],
                        requestedDeliveryDate: $data['receiptDate'],
                        revision: $data['itemRevision'],
                        site: $site
                    );
                },
            );
        }

        return new RequestForQuotation(
            iri: $structure['@id'],
            id: $structure['requestForQuotationCode'],
            erp: (int) $structure['site']['siteID'],
            buyerEmail: $structure['buyerEmail'],
            supplierNumber: implode(', ', $structure['businessPartnerCodes']),
            returnDate: $structure['responseDate'],
            status: RequestForQuotationStatus::from($structure['status']),
            lines: $lines,
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        if (RequestForQuotation::class !== $resource) {
            return false;
        }

        return RequestForQuotation::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = RequestForQuotation::getCollectionTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list RFQs structure.', previous: $e);
        }

        return Vector::fromArray($collection['hydra:member'])->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): never
    {
        throw new FailedTransformationException('Pagination is not supported for RFQ resource.');
    }
}
