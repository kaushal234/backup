<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\MaterialRequirementsPlanning;
use DateTime;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type;

/**
 * @implements ResourceTransformerInterface<MaterialRequirementsPlanning>
 */
final class MaterialRequirementsPlanningResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        if (MaterialRequirementsPlanning::class !== $resource) {
            return false;
        }

        return MaterialRequirementsPlanning::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): MaterialRequirementsPlanning
    {
        try {
            $structure = MaterialRequirementsPlanning::getTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into an MRP structure.', previous: $e);
        }

        return new MaterialRequirementsPlanning(
            iri: $structure['@id'],
            erp: (int) mb_substr($structure['item'], 0, 3),
            purchaseOrder: null,
            supplierNumber: $structure['buyFromBusinessPartner'],
            warehouse: null,
            partNumber: mb_substr($structure['item'], 3),
            supplierPartNumber: $structure['supplierPartNumber'],
            description: $structure['itemDescription'],
            orderedQuantity: $structure['quantity'],
            revision: $structure['status'],
            plannedOrderDate: (new DateTime($structure['plannedStartDate']))->format('Y-m-d'),
            plannedDeliveryDate: (new DateTime($structure['plannedFinishDate']))->format('Y-m-d'),
            vendorPartNumber: null,
            price: $structure['price'],
            currency: $structure['currency'],
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        if (MaterialRequirementsPlanning::class !== $resource) {
            return false;
        }

        return MaterialRequirementsPlanning::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = MaterialRequirementsPlanning::getCollectionTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list MRPs structures.', previous: $e);
        }

        return Vector::fromArray($collection['hydra:member'])->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        if (MaterialRequirementsPlanning::class !== $resource) {
            return false;
        }

        return MaterialRequirementsPlanning::getPageTypeStructure()->matches($data);
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        try {
            $collection = MaterialRequirementsPlanning::getPageTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of MRPs structures.', previous: $e);
        }

        $items = Vector::fromArray($collection['hydra:member'])->map($this->transform(...));

        $totalItems = $data['hydra:totalItems'];
        $hasNext = Iter\contains_key($data['hydra:view'], 'hydra:next');
        $hasPrevious = Iter\contains_key($data['hydra:view'], 'hydra:previous');

        return new Page($page, $itemsPerPage, $totalItems, $hasNext, $hasPrevious, $items);
    }
}
