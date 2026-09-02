<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Resource\BusinessUnit;
use App\Sdk\Resource\Department;
use App\Sdk\Resource\Phone;
use App\Sdk\Resource\Photo;
use App\Sdk\Resource\Region;
use App\Sdk\Resource\Representative;
use App\Security\User\Address;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Type;
use Psl\Vec;

/**
 * @implements ResourceTransformerInterface<Representative>
 */
final class RepresentativeResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        if (Representative::class !== $resource) {
            return false;
        }

        return Representative::getTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transform(mixed $data): Representative
    {
        try {
            $structure = Representative::getTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a representative structure.', previous: $e);
        }

        return new Representative(
            iri: $structure['@id'],
            id: $structure['id'],
            firstname: $structure['firstname'],
            lastname: $structure['lastname'],
            email: $structure['email'],
            businessUnit: new BusinessUnit(
                iri: $structure['businessUnit']['@id'],
                name: $structure['businessUnit']['name'],
                region: new Region(
                    iri: $structure['businessUnit']['region']['@id'],
                    name: $structure['businessUnit']['region']['name'],
                ),
            ),
            department: null !== $structure['department'] ? new Department(
                iri: $structure['department']['@id'],
                name: $structure['department']['name'],
            ) : null,
            jobTitle: $structure['jobTitle'],
            phones: Vec\map($structure['phones'] ?? [], static fn ($phone) => new Phone(
                iri: $phone['@id'],
                type: $phone['type'],
                number: $phone['number'],
            )),
            address: new Address(
                firstLine: $structure['premise']['address']['street1'] ?? '',
                secondLine: $structure['premise']['address']['street2'] ?? '',
                city: $structure['premise']['address']['city'] ?? '',
                state: $structure['premise']['address']['state'] ?? '',
                country: $structure['premise']['address']['country'] ?? '',
                zipCode: $structure['premise']['address']['postalCode'] ?? '',
            ),
            photo: $structure['photo'] ? new Photo(
                iri: $structure['photo']['@id'],
                id: $structure['photo']['id'],
                filePath: $structure['photo']['filePath'],
                createdAt: $structure['photo']['createdAt'],
            ) : null,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supportsCollection(string $resource, mixed $data): bool
    {
        if (Representative::class !== $resource) {
            return false;
        }

        return Representative::getCollectionTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = Representative::getCollectionTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list comment structure.', previous: $e);
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
