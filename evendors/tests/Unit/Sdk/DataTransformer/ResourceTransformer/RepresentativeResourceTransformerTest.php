<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\RepresentativeResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\Representative;
use App\Sdk\Resource\RequestForQuotation;
use App\Sdk\Resource\ResourceInterface;

use function count;

/**
 * @extends AbstractResourceTransformerTest<RequestForQuotation>
 *
 * @group unit
 */
final class RepresentativeResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield 'with all data, nominal case' => [[
            '@id' => '/representatives/1',
            'id' => 1,
            'email' => 'some email',
            'firstname' => 'some firstname',
            'lastname' => 'some lastname',
            'jobTitle' => 'some jobTitle',
            'businessUnit' => [
                '@id' => '/businessunits/1',
                '@type' => 'BusinessUnit',
                'name' => 'some name',
                'region' => [
                    '@id' => '/regions/1',
                    '@type' => 'Region',
                    'name' => 'some name',
                ],
            ],
            'department' => [
                '@id' => '/departments/1',
                '@type' => 'Department',
                'name' => 'some name',
            ],
            'phones' => [[
                '@id' => '/phones/1',
                '@type' => 'Phone',
                'type' => 'some type',
                'number' => 'some number',
            ]],
            'photo' => [
                '@id' => '/photos/1',
                '@type' => 'PeopleFile',
                'id' => 1,
                'filePath' => 'some file path',
                'createdAt' => 'some createdAt',
            ],
        ]];

        yield 'with some nullable values' => [[
            '@id' => '/representatives/2',
            'id' => 1,
            'email' => 'some email',
            'firstname' => 'some firstname',
            'lastname' => 'some lastname',
            'jobTitle' => 'some jobTitle',
            'businessUnit' => [
                '@id' => '/businessunits/2',
                '@type' => 'BusinessUnit',
                'name' => 'some name',
                'region' => [
                    '@id' => '/regions/2',
                    '@type' => 'Region',
                    'name' => 'some name',
                ],
            ],
            'department' => [
                '@id' => '/departments/2',
                '@type' => 'Department',
                'name' => 'some name',
            ],
            'phones' => [[
                '@id' => '/phones/2',
                '@type' => 'Phone',
                'type' => 'some type',
                'number' => 'some number',
            ]],
            'photo' => null, // (*)
        ]];

        yield 'with empty collections' => [[
            '@id' => '/representatives/3',
            'id' => 1,
            'email' => 'some email',
            'firstname' => 'some firstname',
            'lastname' => 'some lastname',
            'jobTitle' => 'some jobTitle',
            'businessUnit' => [
                '@id' => '/businessunits/3',
                '@type' => 'BusinessUnit',
                'name' => 'some name',
                'region' => [
                    '@id' => '/regions/3',
                    '@type' => 'Region',
                    'name' => 'some name',
                ],
            ],
            'department' => [
                '@id' => '/departments/3',
                '@type' => 'Department',
                'name' => 'some name',
            ],
            'phones' => [], // (*)
            'photo' => null,
        ]];

        yield 'with optional fields removed' => [[
            '@id' => '/representatives/2',
            'id' => 1,
            'email' => 'some email',
            'firstname' => 'some firstname',
            'lastname' => 'some lastname',
            'jobTitle' => 'some jobTitle',
            'businessUnit' => [
                '@id' => '/businessunits/2',
                '@type' => 'BusinessUnit',
                'name' => 'some name',
                'region' => [
                    '@id' => '/regions/2',
                    '@type' => 'Region',
                    'name' => 'some name',
                ],
            ],
            'department' => [
                '@id' => '/departments/2',
                '@type' => 'Department',
                'name' => 'some name',
            ],
            // 'phones' => [], // (*)
            'photo' => null,
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield 'no data provided' => [[]];

        yield 'extra field' => [['foo' => 'bar']];

        // not allowed nullable field  :
        // 'email' => null,
        // 'phones' => [],
        yield 'not allowed nullable field' => [[
            '@id' => '/representatives/3',
            'id' => 1,
            'email' => null,
            'firstname' => 'some firstname',
            'lastname' => 'some lastname',
            'jobTitle' => 'some jobTitle',
            'businessUnit' => [
                '@id' => '/businessunits/3',
                '@type' => 'BusinessUnit',
                'name' => 'some name',
                'region' => [
                    '@id' => '/regions/3',
                    '@type' => 'Region',
                    'name' => 'some name',
                ],
            ],
            'department' => [
                '@id' => '/departments/3',
                '@type' => 'Department',
                'name' => 'some name',
            ],
            'phones' => [],
            'photo' => null,
        ]];

        // incorrect types :
        // 'id' => '1',
        // 'photo'['id'] => '1'
        yield 'incorrect types' => [[
            '@id' => '/representatives/1',
            'id' => '1',
            'email' => 'some email',
            'firstname' => 'some firstname',
            'lastname' => 'some lastname',
            'jobTitle' => 'some jobTitle',
            'businessUnit' => [
                '@id' => '/businessunits/1',
                '@type' => 'BusinessUnit',
                'name' => 'some name',
                'region' => [
                    '@id' => '/regions/1',
                    '@type' => 'Region',
                    'name' => 'some name',
                ],
            ],
            'department' => [
                '@id' => '/departments/1',
                '@type' => 'Department',
                'name' => 'some name',
            ],
            'phones' => [[
                '@id' => '/phones/1',
                '@type' => 'Phone',
                'type' => 'some type',
                'number' => 'some number',
            ]],
            'photo' => [
                '@id' => '/photos/1',
                '@type' => 'PeopleFile',
                'id' => '1',
                'filePath' => 'some file path',
                'createdAt' => 'some createdAt',
            ],
        ]];
    }

    protected function getResourceClass(): string
    {
        return Representative::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new RepresentativeResourceTransformer();
    }

    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(Representative::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['email'], $resource->email);
        self::assertSame($structure['firstname'], $resource->firstname);
        self::assertSame($structure['lastname'], $resource->lastname);
        self::assertSame($structure['jobTitle'], $resource->jobTitle);

        self::assertSame($structure['businessUnit']['@id'], $resource->businessUnit->iri);
        self::assertSame($structure['businessUnit']['name'], $resource->businessUnit->name);
        self::assertSame($structure['businessUnit']['region']['@id'], $resource->businessUnit->region->iri);
        self::assertSame($structure['businessUnit']['region']['name'], $resource->businessUnit->region->name);

        self::assertSame($structure['department']['@id'], $resource->department->iri);
        self::assertSame($structure['department']['name'], $resource->department->name);

        if (count($structure['phones'] ?? []) > 0) {
            self::assertSame($structure['phones'][0]['@id'], $resource->phones[0]->iri);
            self::assertSame($structure['phones'][0]['type'], $resource->phones[0]->type);
            self::assertSame($structure['phones'][0]['number'], $resource->phones[0]->number);
        }

        if (null !== $structure['photo']) {
            self::assertSame($structure['photo']['@id'], $resource->photo->iri);
            self::assertSame($structure['photo']['id'], $resource->photo->id);
            self::assertSame($structure['photo']['filePath'], $resource->photo->filePath);
            self::assertSame($structure['photo']['createdAt'], $resource->photo->createdAt);
        }
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
