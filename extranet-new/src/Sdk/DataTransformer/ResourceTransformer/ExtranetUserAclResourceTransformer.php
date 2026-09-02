<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\BusinessUnit;
use App\Sdk\Resource\Customer;
use App\Sdk\Resource\CustomerRelationshipTeam;
use App\Sdk\Resource\ExtranetUserAcl;
use App\Sdk\Resource\Group;
use App\Sdk\Resource\Location;
use App\Sdk\Resource\LocationAddress;
use App\Sdk\Resource\LocationContact;
use App\Sdk\Resource\Photo;
use App\Sdk\Resource\Representative;
use App\Sdk\Utils\IriToId;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Type\Exception\AssertException;

class ExtranetUserAclResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return ExtranetUserAcl::class === $resource && ExtranetUserAcl::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): ExtranetUserAcl
    {
        try {
            $structure = ExtranetUserAcl::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into an extranet user ACL structure.', previous: $e);
        }

        $salesRepresentative = null !== $structure['crt']['salesRepresentative'] ? new Representative(
            iri: $structure['crt']['salesRepresentative']['@id'],
            id: IriToId::iriToId($structure['crt']['salesRepresentative']['@id']),
            lastname: $structure['crt']['salesRepresentative']['lastname'],
            firstname: $structure['crt']['salesRepresentative']['firstname'],
            email: $structure['crt']['salesRepresentative']['email'],
            businessUnit: new BusinessUnit(
                iri: $structure['crt']['salesRepresentative']['businessUnit']['@id'],
                name: $structure['crt']['salesRepresentative']['businessUnit']['name'],
                location: new Location(
                    iri: $structure['crt']['salesRepresentative']['businessUnit']['location']['@id'],
                    name: $structure['crt']['salesRepresentative']['businessUnit']['location']['name'],
                ),
            ),
            photo: null !== $structure['crt']['salesRepresentative']['photo'] ? new Photo(
                iri: $structure['crt']['salesRepresentative']['photo']['@id'],
                id: $structure['crt']['salesRepresentative']['photo']['id'],
                filePath: $structure['crt']['salesRepresentative']['photo']['filePath'],
            ) : null,
        ) : null;

        $serviceRepresentative = null !== $structure['crt']['serviceRepresentative'] ? new Representative(
            iri: $structure['crt']['serviceRepresentative']['@id'],
            id: IriToId::iriToId($structure['crt']['serviceRepresentative']['@id']),
            lastname: $structure['crt']['serviceRepresentative']['lastname'],
            firstname: $structure['crt']['serviceRepresentative']['firstname'],
            email: $structure['crt']['serviceRepresentative']['email'],
            businessUnit: new BusinessUnit(
                iri: $structure['crt']['serviceRepresentative']['businessUnit']['@id'],
                name: $structure['crt']['serviceRepresentative']['businessUnit']['name'],
                location: new Location(
                    iri: $structure['crt']['serviceRepresentative']['businessUnit']['location']['@id'],
                    name: $structure['crt']['serviceRepresentative']['businessUnit']['location']['name'],
                ),
            ),
            photo: null !== $structure['crt']['serviceRepresentative']['photo'] ? new Photo(
                iri: $structure['crt']['serviceRepresentative']['photo']['@id'],
                id: $structure['crt']['serviceRepresentative']['photo']['id'],
                filePath: $structure['crt']['serviceRepresentative']['photo']['filePath'],
            ) : null,
        ) : null;

        $partsRepresentative = null !== $structure['crt']['partsRepresentative'] ? new Representative(
            iri: $structure['crt']['partsRepresentative']['@id'],
            id: IriToId::iriToId($structure['crt']['partsRepresentative']['@id']),
            lastname: $structure['crt']['partsRepresentative']['lastname'],
            firstname: $structure['crt']['partsRepresentative']['firstname'],
            email: $structure['crt']['partsRepresentative']['email'],
            businessUnit: new BusinessUnit(
                iri: $structure['crt']['partsRepresentative']['businessUnit']['@id'],
                name: $structure['crt']['partsRepresentative']['businessUnit']['name'],
                location: new Location(
                    iri: $structure['crt']['partsRepresentative']['businessUnit']['location']['@id'],
                    name: $structure['crt']['partsRepresentative']['businessUnit']['location']['name'],
                ),
            ),
            photo: null !== $structure['crt']['partsRepresentative']['photo'] ? new Photo(
                iri: $structure['crt']['partsRepresentative']['photo']['@id'],
                id: $structure['crt']['partsRepresentative']['photo']['id'],
                filePath: $structure['crt']['partsRepresentative']['photo']['filePath'],
            ) : null,
        ) : null;

        $erpLocation = null !== $structure['crt']['erpLocation'] ? new Location(
            iri: $structure['crt']['erpLocation']['@id'],
            name: $structure['crt']['erpLocation']['name'],
            contact: [] !== $structure['crt']['erpLocation']['contact'] ? new LocationContact(
                telephone: $structure['crt']['erpLocation']['contact']['telephone'] ?? null,
                email: null,
            ) : null,
            address: [] !== $structure['crt']['erpLocation']['address'] ? new LocationAddress(
                street: $structure['crt']['erpLocation']['address']['street1'] ?? null,
                city: $structure['crt']['erpLocation']['address']['city'] ?? null,
                country: $structure['crt']['erpLocation']['address']['country'] ?? null,
                state: $structure['crt']['erpLocation']['address']['state'] ?? null,
                postalCode: $structure['crt']['erpLocation']['address']['postalCode'] ?? null,
            ) : null,
        ) : null;

        $partsLocation = null !== $structure['crt']['partsLocation'] ? new Location(
            iri: $structure['crt']['partsLocation']['@id'],
            name: $structure['crt']['partsLocation']['name'],
            contact: [] !== $structure['crt']['partsLocation']['contact'] ? new LocationContact(
                telephone: $structure['crt']['partsLocation']['contact']['telephone'] ?? null,
                email: $structure['crt']['partsLocation']['contact']['sparePartsEmail'] ?? null,
            ) : null,
            address: [] !== $structure['crt']['partsLocation']['address'] ? new LocationAddress(
                street: $structure['crt']['partsLocation']['address']['street1'] ?? null,
                city: $structure['crt']['partsLocation']['address']['city'] ?? null,
                country: $structure['crt']['partsLocation']['address']['country'] ?? null,
                state: $structure['crt']['partsLocation']['address']['state'] ?? null,
                postalCode: $structure['crt']['partsLocation']['address']['postalCode'] ?? null,
            ) : null,
        ) : null;

        $serviceLocation = null !== $structure['crt']['serviceLocation'] ? new Location(
            iri: $structure['crt']['serviceLocation']['@id'],
            name: $structure['crt']['serviceLocation']['name'],
            contact: [] !== $structure['crt']['serviceLocation']['contact'] ? new LocationContact(
                telephone: $structure['crt']['serviceLocation']['contact']['telephone'] ?? null,
                email: $structure['crt']['serviceLocation']['contact']['serviceHubEmail'] ?? null,
            ) : null,
            address: [] !== $structure['crt']['serviceLocation']['address'] ? new LocationAddress(
                street: $structure['crt']['serviceLocation']['address']['street1'] ?? null,
                city: $structure['crt']['serviceLocation']['address']['city'] ?? null,
                country: $structure['crt']['serviceLocation']['address']['country'] ?? null,
                state: $structure['crt']['serviceLocation']['address']['state'] ?? null,
                postalCode: $structure['crt']['serviceLocation']['address']['postalCode'] ?? null,
            ) : null,
        ) : null;

        $customerRelationshipTeam = new CustomerRelationshipTeam(
            iri: $structure['crt']['@id'],
            salesRepresentative: $salesRepresentative,
            serviceRepresentative: $serviceRepresentative,
            partsRepresentative: $partsRepresentative,
            erpLocation: $erpLocation,
            serviceLocation: $serviceLocation,
            partsLocation: $partsLocation,
            customer: new Customer(
                iri: $structure['crt']['customer']['@id'],
                name: $structure['crt']['customer']['name'],
                legacyId: $structure['crt']['customer']['legacyId'] ?? null,
            ),
        );

        return new ExtranetUserAcl(
            iri: $structure['@id'],
            group: new Group(
                iri: $structure['extranetUserGroup']['@id'],
                name: $structure['extranetUserGroup']['name'],
            ),
            customerRelationshipTeam: $customerRelationshipTeam,
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return ExtranetUserAcl::class === $resource && ExtranetUserAcl::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = ExtranetUserAcl::getCollectionTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of extranet user ACL structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        return Vector::fromArray($members)->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        throw new FailedTransformationException('Pagination is not supported for Extranet User Acl resource.');
    }
}
