<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\Country;
use App\Sdk\Resource\User;
use App\Sdk\Resource\UserAddress;
use App\Sdk\Resource\UserContact;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Type\Exception\AssertException;

class UserResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return User::class === $resource && User::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): User
    {
        try {
            $structure = User::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into an User structure.', previous: $e);
        }

        $address = new UserAddress(
            street: $structure['address']['street1'],
            street2: $structure['address']['street2'],
            city: $structure['address']['city'],
            state: $structure['address']['state'],
            postalCode: $structure['address']['postalCode'],
            country: null !== $structure['extranetUserProfile']['country'] ? new Country(
                iri: $structure['extranetUserProfile']['country']['@id'],
                id: $structure['extranetUserProfile']['country']['id'],
                name: $structure['extranetUserProfile']['country']['name'],
                isoCode2: $structure['extranetUserProfile']['country']['isoCode2'] ?? null,
            ) : null,
        );

        $phones = [];
        $phoneTypes = ['reception', 'phone', 'mobile', 'fax'];
        foreach ($structure['phones'] as $phone) {
            $phones[$phone['type']] = \in_array($phone['type'], $phoneTypes, true) ? $phone['number'] : null;
        }

        $contact = new UserContact(
            reception: $phones['reception'] ?? null,
            phone: $phones['phone'] ?? null,
            mobile: $phones['mobile'] ?? null,
            fax: $phones['fax'] ?? null,
        );

        return new User(
            iri: $structure['@id'],
            id: $structure['id'],
            profileIri: $structure['extranetUserProfile']['@id'],
            lastname: $structure['lastname'],
            firstname: $structure['firstname'],
            email: $structure['email'],
            title: $structure['extranetUserProfile']['jobTitle'],
            division: $structure['extranetUserProfile']['division'],
            department: $structure['extranetUserProfile']['department'],
            language: $structure['extranetUserProfile']['language'],
            address: $address,
            contact: $contact,
            passwordExpirationDate: $structure['passwordExpirationDate'],
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        throw new FailedTransformationException('Collection is not supported for User resource.');
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        throw new FailedTransformationException('Pagination is not supported for User resource.');
    }
}
