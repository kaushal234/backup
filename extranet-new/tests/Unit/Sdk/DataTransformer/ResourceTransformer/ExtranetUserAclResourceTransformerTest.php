<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ExtranetUserAclResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\ExtranetUserAcl;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Utils\IriToId;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class ExtranetUserAclResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/extranet_user-acl/1',
            'extranetUserGroup' => [
                '@id' => '/groups/1',
                'name' => 'some group',
            ],
            'crt' => [
                '@id' => '/customer_relationship_teams/1',
                'salesRepresentative' => [
                    '@id' => '/people/1',
                    'lastname' => 'sales',
                    'firstname' => 'representative',
                    'email' => 'sales.representative@extranet.com',
                    'businessUnit' => [
                        '@id' => '/business_units/1',
                        'name' => 'some sales name',
                        'location' => [
                            '@id' => '/locations/1',
                            'name' => 'sales location',
                            'address' => [
                                'street1' => 'sales street 1',
                                'city' => 'sales city',
                                'country' => 'sales country',
                                'state' => 'sales state',
                                'postalCode' => '39278',
                            ],
                            'contact' => [
                                'telephone' => 'some sales phone number',
                                'sparePartsEmail' => 'some sales parts email',
                                'serviceHubEmail' => 'some sales hub email',
                            ],
                        ],
                    ],
                    'photo' => [
                        '@id' => '/photo/1',
                        'id' => 1,
                        'filePath' => 'some path',
                    ],
                ],
                'serviceRepresentative' => [
                    '@id' => '/people/2',
                    'lastname' => 'service',
                    'firstname' => 'representative',
                    'email' => 'service.representative@extranet.com',
                    'businessUnit' => [
                        '@id' => '/business_units/2',
                        'name' => 'some service name',
                        'location' => [
                            '@id' => '/locations/2',
                            'name' => 'service location',
                            'address' => [
                                'street1' => 'service street 1',
                                'city' => 'service city',
                                'country' => 'service country',
                                'state' => 'service state',
                                'postalCode' => '39279',
                            ],
                            'contact' => [
                                'telephone' => 'some service phone number',
                                'sparePartsEmail' => 'some service parts email',
                                'serviceHubEmail' => 'some service hub email',
                            ],
                        ],
                    ],
                    'photo' => [
                        '@id' => '/photo/2',
                        'id' => 2,
                        'filePath' => 'some service path',
                    ],
                ],
                'partsRepresentative' => [
                    '@id' => '/people/3',
                    'lastname' => 'parts',
                    'firstname' => 'representative',
                    'email' => 'parts.representative@extranet.com',
                    'businessUnit' => [
                        '@id' => '/business_units/3',
                        'name' => 'some parts name',
                        'location' => [
                            '@id' => '/locations/3',
                            'name' => 'parts location',
                            'address' => [
                                'street1' => 'parts street 1',
                                'city' => 'parts city',
                                'country' => 'parts country',
                                'state' => 'parts state',
                                'postalCode' => '39280',
                            ],
                            'contact' => [
                                'telephone' => 'some parts phone number',
                                'sparePartsEmail' => 'some parts parts email',
                                'serviceHubEmail' => 'some parts hub email',
                            ],
                        ],
                    ],
                    'photo' => [
                        '@id' => '/photo/3',
                        'id' => 3,
                        'filePath' => 'some parts path',
                    ],
                ],
                'erpLocation' => [
                    '@id' => '/locations/4',
                    'name' => 'erp location',
                    'address' => [
                        'street1' => 'erp street 1',
                        'city' => 'erp city',
                        'country' => 'erp country',
                        'state' => 'erp state',
                        'postalCode' => '39281',
                    ],
                    'contact' => [
                        'telephone' => 'some erp phone number',
                        'sparePartsEmail' => 'some erp parts email',
                        'serviceHubEmail' => 'some erp hub email',
                    ],
                ],
                'serviceLocation' => [
                    '@id' => '/locations/5',
                    'name' => 'erp location',
                    'address' => [
                        'street1' => 'service street 1',
                        'city' => 'service city',
                        'country' => 'service country',
                        'state' => 'service state',
                        'postalCode' => '39282',
                    ],
                    'contact' => [
                        'telephone' => 'some service phone number',
                        'sparePartsEmail' => 'some service parts email',
                        'serviceHubEmail' => 'some service hub email',
                    ],
                ],
                'partsLocation' => [
                    '@id' => '/locations/6',
                    'name' => 'parts location',
                    'address' => [
                        'street1' => 'parts street 1',
                        'city' => 'parts city',
                        'country' => 'parts country',
                        'state' => 'parts state',
                        'postalCode' => '39283',
                    ],
                    'contact' => [
                        'telephone' => 'some parts phone number',
                        'sparePartsEmail' => 'some parts parts email',
                        'serviceHubEmail' => 'some parts hub email',
                    ],
                ],
                'customer' => [
                    '@id' => '/customer/1',
                    'name' => 'customer name',
                ],
            ],
        ]];

        yield [[
            '@id' => '/extranet_user-acl/1',
            'extranetUserGroup' => [
                '@id' => '/groups/1',
                'name' => 'some group',
            ],
            'crt' => [
                '@id' => '/customer_relationship_teams/1',
                'salesRepresentative' => [
                    '@id' => '/people/1',
                    'lastname' => 'sales',
                    'firstname' => 'representative',
                    'email' => 'sales.representative@extranet.com',
                    'businessUnit' => [
                        '@id' => '/business_units/1',
                        'name' => 'some sales name',
                        'location' => [
                            '@id' => '/locations/1',
                            'name' => 'sales location',
                            'address' => [
                                'street1' => 'sales street 1',
                                'city' => 'sales city',
                                'country' => 'sales country',
                                'state' => null,
                                'postalCode' => null,
                            ],
                            'contact' => [
                                'telephone' => 'some sales phone number',
                                'sparePartsEmail' => null,
                                'serviceHubEmail' => null,
                            ],
                        ],
                    ],
                    'photo' => [
                        '@id' => '/photo/1',
                        'id' => 1,
                        'filePath' => 'some path',
                    ],
                ],
                'serviceRepresentative' => [
                    '@id' => '/people/2',
                    'lastname' => 'service',
                    'firstname' => 'representative',
                    'email' => 'service.representative@extranet.com',
                    'businessUnit' => [
                        '@id' => '/business_units/2',
                        'name' => 'some service name',
                        'location' => [
                            '@id' => '/locations/2',
                            'name' => 'service location',
                            'address' => [
                                'street1' => 'service street 1',
                                'city' => 'service city',
                                'country' => 'service country',
                                'state' => null,
                                'postalCode' => null,
                            ],
                            'contact' => [
                                'telephone' => 'some service phone number',
                                'sparePartsEmail' => null,
                                'serviceHubEmail' => null,
                            ],
                        ],
                    ],
                    'photo' => [
                        '@id' => '/photo/2',
                        'id' => 2,
                        'filePath' => 'some service path',
                    ],
                ],
                'partsRepresentative' => [
                    '@id' => '/people/3',
                    'lastname' => 'parts',
                    'firstname' => 'representative',
                    'email' => 'parts.representative@extranet.com',
                    'businessUnit' => [
                        '@id' => '/business_units/3',
                        'name' => 'some parts name',
                        'location' => [
                            '@id' => '/locations/3',
                            'name' => 'parts location',
                            'address' => [
                                'street1' => 'parts street 1',
                                'city' => 'parts city',
                                'country' => 'parts country',
                                'state' => null,
                                'postalCode' => null,
                            ],
                            'contact' => [
                                'telephone' => 'some parts phone number',
                                'sparePartsEmail' => null,
                                'serviceHubEmail' => null,
                            ],
                        ],
                    ],
                    'photo' => [
                        '@id' => '/photo/3',
                        'id' => 3,
                        'filePath' => 'some parts path',
                    ],
                ],
                'erpLocation' => [
                    '@id' => '/locations/4',
                    'name' => 'erp location',
                    'address' => [
                        'street1' => 'erp street 1',
                        'city' => 'erp city',
                        'country' => 'erp country',
                        'state' => null,
                        'postalCode' => null,
                    ],
                    'contact' => [
                        'telephone' => 'some erp phone number',
                        'sparePartsEmail' => null,
                        'serviceHubEmail' => null,
                    ],
                ],
                'serviceLocation' => [
                    '@id' => '/locations/5',
                    'name' => 'erp location',
                    'address' => [
                        'street1' => 'service street 1',
                        'city' => 'service city',
                        'country' => 'service country',
                        'state' => null,
                        'postalCode' => null,
                    ],
                    'contact' => [
                        'telephone' => 'some service phone number',
                        'sparePartsEmail' => null,
                        'serviceHubEmail' => null,
                    ],
                ],
                'partsLocation' => [
                    '@id' => '/locations/6',
                    'name' => 'parts location',
                    'address' => [
                        'street1' => 'parts street 1',
                        'city' => 'parts city',
                        'country' => 'parts country',
                        'state' => null,
                        'postalCode' => null,
                    ],
                    'contact' => [
                        'telephone' => 'some parts phone number',
                        'sparePartsEmail' => null,
                        'serviceHubEmail' => null,
                    ],
                ],
                'customer' => [
                    '@id' => '/customer/1',
                    'name' => 'customer name',
                ],
            ],
        ]];

        yield [[
            '@id' => '/extranet_user-acl/1',
            'extranetUserGroup' => [
                '@id' => '/groups/1',
                'name' => 'some group',
            ],
            'crt' => [
                '@id' => '/customer_relationship_teams/1',
                'salesRepresentative' => [
                    '@id' => '/people/1',
                    'lastname' => 'sales',
                    'firstname' => 'representative',
                    'email' => 'sales.representative@extranet.com',
                    'businessUnit' => [
                        '@id' => '/business_units/1',
                        'name' => 'some sales name',
                        'location' => [
                            '@id' => '/locations/1',
                            'name' => 'sales location',
                            'address' => [],
                            'contact' => [],
                        ],
                    ],
                    'photo' => null,
                ],
                'serviceRepresentative' => [
                    '@id' => '/people/2',
                    'lastname' => 'service',
                    'firstname' => 'representative',
                    'email' => 'service.representative@extranet.com',
                    'businessUnit' => [
                        '@id' => '/business_units/2',
                        'name' => 'some service name',
                        'location' => [
                            '@id' => '/locations/2',
                            'name' => 'service location',
                            'address' => [],
                            'contact' => [],
                        ],
                    ],
                    'photo' => null,
                ],
                'partsRepresentative' => [
                    '@id' => '/people/3',
                    'lastname' => 'parts',
                    'firstname' => 'representative',
                    'email' => 'parts.representative@extranet.com',
                    'businessUnit' => [
                        '@id' => '/business_units/3',
                        'name' => 'some parts name',
                        'location' => [
                            '@id' => '/locations/3',
                            'name' => 'parts location',
                            'address' => [],
                            'contact' => [],
                        ],
                    ],
                    'photo' => null,
                ],
                'erpLocation' => [
                    '@id' => '/locations/4',
                    'name' => 'erp location',
                    'address' => [],
                    'contact' => [],
                ],
                'serviceLocation' => [
                    '@id' => '/locations/5',
                    'name' => 'erp location',
                    'address' => [],
                    'contact' => [],
                ],
                'partsLocation' => [
                    '@id' => '/locations/6',
                    'name' => 'parts location',
                    'address' => [],
                    'contact' => [],
                ],
                'customer' => [
                    '@id' => '/customer/1',
                    'name' => 'customer name',
                ],
            ],
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@id' => '/extranet_user-acl/1',
            'extranetUserGroup' => 'name',
            'crt' => [
                '@id' => '/customer_relationship_teams/1',
                'salesRepresentative' => null,
                'serviceRepresentative' => null,
                'partsRepresentative' => null,
                'erpLocation' => null,
                'serviceLocation' => null,
                'partsLocation' => null,
                'customer' => null,
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
        return ExtranetUserAcl::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new ExtranetUserAclResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(ExtranetUserAcl::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['extranetUserGroup']['@id'], $resource->group->iri);
        self::assertSame($structure['extranetUserGroup']['name'], $resource->group->name);
        self::assertSame($structure['crt']['@id'], $resource->customerRelationshipTeam->iri);

        self::assertSame($structure['crt']['salesRepresentative']['@id'], $resource->customerRelationshipTeam->salesRepresentative->iri);
        self::assertSame(IriToId::iriToId($structure['crt']['salesRepresentative']['@id']), $resource->customerRelationshipTeam->salesRepresentative->id);
        self::assertSame($structure['crt']['salesRepresentative']['lastname'], $resource->customerRelationshipTeam->salesRepresentative->lastname);
        self::assertSame($structure['crt']['salesRepresentative']['firstname'], $resource->customerRelationshipTeam->salesRepresentative->firstname);
        self::assertSame($structure['crt']['salesRepresentative']['email'], $resource->customerRelationshipTeam->salesRepresentative->email);
        self::assertSame($structure['crt']['salesRepresentative']['businessUnit']['@id'], $resource->customerRelationshipTeam->salesRepresentative->businessUnit->iri);
        self::assertSame($structure['crt']['salesRepresentative']['businessUnit']['name'], $resource->customerRelationshipTeam->salesRepresentative->businessUnit->name);
        self::assertSame($structure['crt']['salesRepresentative']['businessUnit']['location']['@id'], $resource->customerRelationshipTeam->salesRepresentative->businessUnit->location->iri);
        self::assertSame($structure['crt']['salesRepresentative']['businessUnit']['location']['name'], $resource->customerRelationshipTeam->salesRepresentative->businessUnit->location->name);
        self::assertSame($structure['crt']['salesRepresentative']['photo']['@id'] ?? null, $resource->customerRelationshipTeam->salesRepresentative->photo?->iri);
        self::assertSame($structure['crt']['salesRepresentative']['photo']['id'] ?? null, $resource->customerRelationshipTeam->salesRepresentative->photo?->id);
        self::assertSame($structure['crt']['salesRepresentative']['photo']['filePath'] ?? null, $resource->customerRelationshipTeam->salesRepresentative->photo?->filePath);

        self::assertSame($structure['crt']['partsRepresentative']['@id'], $resource->customerRelationshipTeam->partsRepresentative->iri);
        self::assertSame(IriToId::iriToId($structure['crt']['partsRepresentative']['@id']), $resource->customerRelationshipTeam->partsRepresentative->id);
        self::assertSame($structure['crt']['partsRepresentative']['lastname'], $resource->customerRelationshipTeam->partsRepresentative->lastname);
        self::assertSame($structure['crt']['partsRepresentative']['firstname'], $resource->customerRelationshipTeam->partsRepresentative->firstname);
        self::assertSame($structure['crt']['partsRepresentative']['email'], $resource->customerRelationshipTeam->partsRepresentative->email);
        self::assertSame($structure['crt']['partsRepresentative']['businessUnit']['@id'], $resource->customerRelationshipTeam->partsRepresentative->businessUnit->iri);
        self::assertSame($structure['crt']['partsRepresentative']['businessUnit']['name'], $resource->customerRelationshipTeam->partsRepresentative->businessUnit->name);
        self::assertSame($structure['crt']['partsRepresentative']['businessUnit']['location']['@id'], $resource->customerRelationshipTeam->partsRepresentative->businessUnit->location->iri);
        self::assertSame($structure['crt']['partsRepresentative']['businessUnit']['location']['name'], $resource->customerRelationshipTeam->partsRepresentative->businessUnit->location->name);
        self::assertSame($structure['crt']['partsRepresentative']['photo']['@id'] ?? null, $resource->customerRelationshipTeam->partsRepresentative->photo?->iri);
        self::assertSame($structure['crt']['partsRepresentative']['photo']['id'] ?? null, $resource->customerRelationshipTeam->partsRepresentative->photo?->id);
        self::assertSame($structure['crt']['partsRepresentative']['photo']['filePath'] ?? null, $resource->customerRelationshipTeam->partsRepresentative->photo?->filePath);

        self::assertSame($structure['crt']['serviceRepresentative']['@id'], $resource->customerRelationshipTeam->serviceRepresentative->iri);
        self::assertSame(IriToId::iriToId($structure['crt']['serviceRepresentative']['@id']), $resource->customerRelationshipTeam->serviceRepresentative->id);
        self::assertSame($structure['crt']['serviceRepresentative']['lastname'], $resource->customerRelationshipTeam->serviceRepresentative->lastname);
        self::assertSame($structure['crt']['serviceRepresentative']['firstname'], $resource->customerRelationshipTeam->serviceRepresentative->firstname);
        self::assertSame($structure['crt']['serviceRepresentative']['email'], $resource->customerRelationshipTeam->serviceRepresentative->email);
        self::assertSame($structure['crt']['serviceRepresentative']['businessUnit']['@id'], $resource->customerRelationshipTeam->serviceRepresentative->businessUnit->iri);
        self::assertSame($structure['crt']['serviceRepresentative']['businessUnit']['name'], $resource->customerRelationshipTeam->serviceRepresentative->businessUnit->name);
        self::assertSame($structure['crt']['serviceRepresentative']['businessUnit']['location']['@id'], $resource->customerRelationshipTeam->serviceRepresentative->businessUnit->location->iri);
        self::assertSame($structure['crt']['serviceRepresentative']['businessUnit']['location']['name'], $resource->customerRelationshipTeam->serviceRepresentative->businessUnit->location->name);
        self::assertSame($structure['crt']['serviceRepresentative']['photo']['@id'] ?? null, $resource->customerRelationshipTeam->serviceRepresentative->photo?->iri);
        self::assertSame($structure['crt']['serviceRepresentative']['photo']['id'] ?? null, $resource->customerRelationshipTeam->serviceRepresentative->photo?->id);
        self::assertSame($structure['crt']['serviceRepresentative']['photo']['filePath'] ?? null, $resource->customerRelationshipTeam->serviceRepresentative->photo?->filePath);

        self::assertSame($structure['crt']['erpLocation']['@id'], $resource->customerRelationshipTeam->erpLocation->iri);
        self::assertSame($structure['crt']['erpLocation']['name'], $resource->customerRelationshipTeam->erpLocation->name);
        self::assertSame($structure['crt']['erpLocation']['address']['street1'] ?? null, $resource->customerRelationshipTeam->erpLocation->address?->street);
        self::assertSame($structure['crt']['erpLocation']['address']['city'] ?? null, $resource->customerRelationshipTeam->erpLocation->address?->city);
        self::assertSame($structure['crt']['erpLocation']['address']['state'] ?? null, $resource->customerRelationshipTeam->erpLocation->address?->state);
        self::assertSame($structure['crt']['erpLocation']['address']['postalCode'] ?? null, $resource->customerRelationshipTeam->erpLocation->address?->postalCode);
        self::assertSame($structure['crt']['erpLocation']['address']['country'] ?? null, $resource->customerRelationshipTeam->erpLocation->address?->country);
        self::assertSame($structure['crt']['erpLocation']['contact']['telephone'] ?? null, $resource->customerRelationshipTeam->erpLocation->contact?->telephone);
        self::assertNull($resource->customerRelationshipTeam->erpLocation->contact?->email);

        self::assertSame($structure['crt']['serviceLocation']['@id'], $resource->customerRelationshipTeam->serviceLocation->iri);
        self::assertSame($structure['crt']['serviceLocation']['name'], $resource->customerRelationshipTeam->serviceLocation->name);
        self::assertSame($structure['crt']['serviceLocation']['address']['street1'] ?? null, $resource->customerRelationshipTeam->serviceLocation->address?->street);
        self::assertSame($structure['crt']['serviceLocation']['address']['city'] ?? null, $resource->customerRelationshipTeam->serviceLocation->address?->city);
        self::assertSame($structure['crt']['serviceLocation']['address']['state'] ?? null, $resource->customerRelationshipTeam->serviceLocation->address?->state);
        self::assertSame($structure['crt']['serviceLocation']['address']['postalCode'] ?? null, $resource->customerRelationshipTeam->serviceLocation->address?->postalCode);
        self::assertSame($structure['crt']['serviceLocation']['address']['country'] ?? null, $resource->customerRelationshipTeam->serviceLocation->address?->country);
        self::assertSame($structure['crt']['serviceLocation']['contact']['telephone'] ?? null, $resource->customerRelationshipTeam->serviceLocation->contact?->telephone);
        self::assertSame($structure['crt']['serviceLocation']['contact']['serviceHubEmail'] ?? null, $resource->customerRelationshipTeam->serviceLocation->contact?->email);

        self::assertSame($structure['crt']['partsLocation']['@id'], $resource->customerRelationshipTeam->partsLocation->iri);
        self::assertSame($structure['crt']['partsLocation']['name'], $resource->customerRelationshipTeam->partsLocation->name);
        self::assertSame($structure['crt']['partsLocation']['address']['street1'] ?? null, $resource->customerRelationshipTeam->partsLocation->address?->street);
        self::assertSame($structure['crt']['partsLocation']['address']['city'] ?? null, $resource->customerRelationshipTeam->partsLocation->address?->city);
        self::assertSame($structure['crt']['partsLocation']['address']['state'] ?? null, $resource->customerRelationshipTeam->partsLocation->address?->state);
        self::assertSame($structure['crt']['partsLocation']['address']['postalCode'] ?? null, $resource->customerRelationshipTeam->partsLocation->address?->postalCode);
        self::assertSame($structure['crt']['partsLocation']['address']['country'] ?? null, $resource->customerRelationshipTeam->partsLocation->address?->country);
        self::assertSame($structure['crt']['partsLocation']['contact']['telephone'] ?? null, $resource->customerRelationshipTeam->partsLocation->contact?->telephone);
        self::assertSame($structure['crt']['partsLocation']['contact']['sparePartsEmail'] ?? null, $resource->customerRelationshipTeam->partsLocation->contact?->email);
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
