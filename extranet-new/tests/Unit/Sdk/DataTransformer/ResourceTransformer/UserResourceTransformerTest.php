<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\DataTransformer\ResourceTransformer\UserResourceTransformer;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\User;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class UserResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/user/1',
            'id' => 1,
            'lastname' => 'pesquet',
            'firstname' => 'thomas',
            'email' => 'thomas.pesquet@nasa.fr',
            'extranetUserProfile' => [
                '@id' => '/profile/1',
                'jobTitle' => 'some title',
                'division' => 'some division',
                'department' => 'some department',
                'language' => 'some language',
                'country' => [
                    '@id' => '/countries/1',
                    'id' => 1,
                    'name' => 'Kinder',
                ],
            ],
            'address' => [
                'street1' => 'street 1',
                'street2' => 'street 2',
                'city' => 'some city',
                'state' => 'some state',
                'postalCode' => 'some postal code',
            ],
            'phones' => [],
            'passwordExpirationDate' => '2025-07-01',
        ]];

        yield [[
            '@id' => '/user/1',
            'id' => 1,
            'lastname' => 'pesquet',
            'firstname' => 'thomas',
            'email' => 'thomas.pesquet@nasa.fr',
            'extranetUserProfile' => [
                '@id' => '/profile/1',
                'jobTitle' => null,
                'division' => null,
                'department' => null,
                'language' => null,
                'country' => null,
            ],
            'address' => [
                'street1' => null,
                'street2' => null,
                'city' => null,
                'state' => null,
                'postalCode' => null,
            ],
            'phones' => [],
            'passwordExpirationDate' => '2025-07-01',
        ]];

        yield [[
            '@id' => '/user/1',
            'id' => 1,
            'lastname' => 'pesquet',
            'firstname' => 'thomas',
            'email' => 'thomas.pesquet@nasa.fr',
            'extranetUserProfile' => [
                '@id' => '/profile/1',
                'jobTitle' => null,
                'division' => null,
                'department' => null,
                'language' => null,
                'country' => null,
            ],
            'address' => [
                'street1' => null,
                'street2' => null,
                'city' => null,
                'state' => null,
                'postalCode' => null,
            ],
            'phones' => [],
            'passwordExpirationDate' => '2025-07-01',
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@id' => '/user/1',
            'id' => 1,
            'lastname' => 'pesquet',
            'firstname' => 'thomas',
            'email' => 'thomas.pesquet@nasa.fr',
            'extranetUserProfile' => [
                '@id' => '/profile/1',
                'jobTitle' => '',
                'division' => '',
                'department' => '',
                'language' => '',
                'country' => [
                    '@id' => '',
                    'id' => '',
                    'name' => '',
                ],
            ],
            'address' => [
                'street1' => '',
                'street2' => '',
                'city' => '',
                'state' => '',
                'postalCode' => '',
            ],
        ]];

        yield [[
            '@id' => '/user/1',
            'id' => 1,
            'lastname' => '',
            'firstname' => '',
            'email' => 'thomas.pesquet@nasa.fr',
            'extranetUserProfile' => [
                '@id' => '/profile/1',
                'jobTitle' => '',
                'division' => '',
                'department' => '',
                'language' => '',
                'country' => [
                    '@id' => '',
                    'id' => '',
                    'name' => '',
                ],
            ],
            'address' => [
                'street1' => '',
                'street2' => '',
                'city' => '',
                'state' => '',
                'postalCode' => '',
            ],
        ]];

        yield [[
            '@id' => '/user/1',
            'id' => 1,
            'lastname' => null,
            'firstname' => null,
            'email' => null,
            'extranetUserProfile' => [
                '@id' => '/profile/1',
                'jobTitle' => '',
                'division' => '',
                'department' => '',
                'language' => '',
                'country' => [
                    '@id' => '',
                    'id' => '',
                    'name' => '',
                ],
            ],
            'address' => [
                'street1' => '',
                'street2' => '',
                'city' => '',
                'state' => '',
                'postalCode' => '',
            ],
        ]];

        yield [[
            '@id' => '/user/1',
            'id' => 1,
            'lastname' => 'pesquet',
            'firstname' => 'thomas',
            'email' => 'thomas.pesquet@nasa.fr',
            'extranetUserProfile' => null,
            'address' => [
                'street1' => '',
                'street2' => '',
                'city' => '',
                'state' => '',
                'postalCode' => '',
            ],
        ]];

        yield [[]];

        yield [[
            'username' => 'some_username',
            'email' => 'some_email',
            'firstname' => 'some_firstname',
            'lastname' => 'some_lastname',
        ]];
    }

    protected function getResourceClass(): string
    {
        return User::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new UserResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(User::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['lastname'], $resource->lastname);
        self::assertSame($structure['firstname'], $resource->firstname);
        self::assertSame($structure['email'], $resource->email);
        self::assertSame($structure['extranetUserProfile']['@id'], $resource->profileIri);
        self::assertSame($structure['extranetUserProfile']['jobTitle'], $resource->title);
        self::assertSame($structure['extranetUserProfile']['division'], $resource->division);
        self::assertSame($structure['extranetUserProfile']['department'], $resource->department);
        self::assertSame($structure['extranetUserProfile']['language'], $resource->language);
        self::assertSame($structure['extranetUserProfile']['country']['@id'] ?? null, $resource->address->country?->iri);
        self::assertSame($structure['extranetUserProfile']['country']['id'] ?? null, $resource->address->country?->id);
        self::assertSame($structure['extranetUserProfile']['country']['name'] ?? null, $resource->address->country?->name);
        self::assertSame($structure['address']['street1'], $resource->address->street);
        self::assertSame($structure['address']['street2'], $resource->address->street2);
        self::assertSame($structure['address']['city'], $resource->address->city);
        self::assertSame($structure['address']['state'], $resource->address->state);
        self::assertSame($structure['address']['postalCode'], $resource->address->postalCode);
        self::assertSame($structure['passwordExpirationDate'], $resource->passwordExpirationDate);
    }

    protected function supportsCollections(): bool
    {
        return false;
    }

    protected function supportsPage(): bool
    {
        return false;
    }
}
