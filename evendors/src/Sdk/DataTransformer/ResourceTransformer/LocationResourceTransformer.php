<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Resource\Currency;
use App\Sdk\Resource\Location;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Type;

/**
 * @implements ResourceTransformerInterface<Location>
 */
final class LocationResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        if (Location::class !== $resource) {
            return false;
        }

        return Location::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): Location
    {
        try {
            $structure = Location::getTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a Location structure.', previous: $e);
        }

        $currency = null !== $structure['currency'] ? new Currency(
            iri: $structure['currency']['@id'],
            id: $structure['currency']['id'],
            name: $structure['currency']['name']
        ) : null;

        return new Location(
            iri: $structure['@id'],
            name: $structure['name'],
            erp: $structure['erp'],
            currency: $currency,
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        if (Location::class !== $resource) {
            return false;
        }

        return Location::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = Location::getCollectionTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list Location structure.', previous: $e);
        }

        return Vector::fromArray($collection['hydra:member'])->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): never
    {
        throw new FailedTransformationException('Pagination is not supported for Location resource.');
    }
}
