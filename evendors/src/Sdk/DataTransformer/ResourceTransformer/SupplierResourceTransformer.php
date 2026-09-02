<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Resource\Supplier;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Type;

/**
 * @implements ResourceTransformerInterface<Supplier>
 */
final class SupplierResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        if (Supplier::class !== $resource) {
            return false;
        }

        return Supplier::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): Supplier
    {
        try {
            $structure = Supplier::getTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a Supplier structure.', previous: $e);
        }

        return new Supplier(
            iri: $structure['@id'],
            name: $structure['name'],
            code: $structure['code'],
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        if (Supplier::class !== $resource) {
            return false;
        }

        return Supplier::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = Supplier::getCollectionTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list Supplier structure.', previous: $e);
        }

        return Vector::fromArray($collection['hydra:member'])->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): never
    {
        throw new FailedTransformationException('Pagination is not supported for Supplier resource.');
    }
}
