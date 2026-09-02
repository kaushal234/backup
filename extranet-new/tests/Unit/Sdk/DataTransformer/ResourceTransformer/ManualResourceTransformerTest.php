<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ManualResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\Manual;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Utils\IriToId;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class ManualResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/manuals/1',
            '@type' => 'manual',
            'id' => 42,
            'createdAt' => '2024-01-01',
            'features' => 'test features',
            'description' => 'this is a description',
            'language' => 'en',
            'status' => 'RELEASED',
            'equipmentRecord' => [
                '@id' => '/equipments/1',
                'id' => 1,
                'serialNumber' => 'T1000',
                'model' => 'T-MAX',
                'legacyId' => 12,
            ],
            'documents' => [
                [
                    '@id' => '/documents/1',
                    'position' => 1,
                    'revision' => '1',
                    'category' => [
                        '@id' => '/categories/1',
                        'name' => 'category',
                    ],
                    'description' => 'this is a description',
                    'otherDescription' => 'this is another description',
                    'type' => 'type',
                    'factoryNumber' => '123456X',
                    'document' => [
                        '@id' => '/files/1',
                        'id' => 1,
                        'filePath' => '.../tmp/test.png',
                        'description' => 'this is a description',
                        'poster' => [
                            '@id' => '/people/1',
                            'lastname' => 'toto',
                            'firstname' => 'tata',
                            'email' => 'email@test.com',
                        ],
                        'createdAt' => '2024-01-01',
                        'extension' => 'png',
                        'size' => 12345,
                    ],
                ],
            ],
        ]];

        yield [[
            '@id' => '/manuals/1',
            '@type' => 'manual',
            'id' => 43,
            'createdAt' => '2024-01-01',
            'features' => null,
            'description' => null,
            'language' => null,
            'status' => 'RELEASED',
            'equipmentRecord' => [
                '@id' => '/equipments/1',
                'id' => 1,
                'serialNumber' => 'T1000',
                'model' => 'T-MAX',
                'legacyId' => 12,
            ],
            'documents' => [],
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@type' => 'manuals',
            'id' => 13,
        ]];

        yield [[
            '@id' => '/manuals/1',
            '@type' => 'manual',
            'id' => 44,
            'createdAt' => '2024-01-01',
            'features' => null,
            'description' => null,
            'language' => null,
            'status' => null,
            'equipment' => null,
            'documents' => [],
        ]];

        yield [[
            '@id' => '/manuals/1',
            '@type' => 'manual',
            'id' => 45,
            'createdAt' => '2024-01-01',
            'features' => null,
            'description' => null,
            'language' => null,
            'status' => null,
            'equipmentRecord' => [
                '@id' => '/equipments/1',
                'id' => 1,
                'serialNumber' => 'T1000',
                'model' => 'T-MAX',
                'legacyId' => 12,
            ],
            'documents' => [],
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
        return Manual::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new ManualResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(Manual::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['createdAt'], $resource->createdAt->format('Y-m-d'));
        self::assertSame($structure['features'], $resource->features);
        self::assertSame($structure['description'], $resource->description);
        self::assertSame($structure['language'], $resource->language);

        self::assertSame($structure['equipmentRecord']['@id'], $resource->equipment->iri);
        self::assertSame($structure['equipmentRecord']['id'], $resource->equipment->id);
        self::assertSame($structure['equipmentRecord']['serialNumber'], $resource->equipment->serialNumber);
        self::assertSame($structure['equipmentRecord']['model'], $resource->equipment->product);
        self::assertSame($structure['equipmentRecord']['legacyId'], $resource->equipment->legacyId);

        if (\count($structure['documents'] ?? []) > 0) {
            self::assertSame($structure['documents'][0]['@id'], $resource->documents[0]->iri);
            self::assertSame(IriToId::iriToId($structure['documents'][0]['@id']), $resource->documents[0]->id);
            self::assertSame($structure['documents'][0]['position'], $resource->documents[0]->position);
            self::assertSame($structure['documents'][0]['revision'], $resource->documents[0]->revision);
            self::assertSame($structure['documents'][0]['description'], $resource->documents[0]->description);
            self::assertSame($structure['documents'][0]['otherDescription'], $resource->documents[0]->otherDescription);
            self::assertSame($structure['documents'][0]['type'], $resource->documents[0]->type);
            self::assertSame($structure['documents'][0]['factoryNumber'], $resource->documents[0]->factoryNumber);

            if (null !== $structure['documents'][0]['document']) {
                self::assertSame($structure['documents'][0]['document']['@id'], $resource->documents[0]->document->iri);
                self::assertSame($structure['documents'][0]['document']['id'], $resource->documents[0]->document->id);
                self::assertSame($structure['documents'][0]['document']['filePath'], $resource->documents[0]->document->filePath);
                self::assertSame($structure['documents'][0]['document']['description'], $resource->documents[0]->document->description);
                self::assertSame($structure['documents'][0]['document']['createdAt'], $resource->documents[0]->document->createdAt->format('Y-m-d'));
                self::assertSame($structure['documents'][0]['document']['extension'], $resource->documents[0]->document->extension);
                self::assertSame($structure['documents'][0]['document']['poster']['@id'], $resource->documents[0]->document->poster->iri);
                self::assertSame($structure['documents'][0]['document']['poster']['lastname'], $resource->documents[0]->document->poster->lastname);
                self::assertSame($structure['documents'][0]['document']['poster']['firstname'], $resource->documents[0]->document->poster->firstname);
                self::assertSame($structure['documents'][0]['document']['poster']['email'], $resource->documents[0]->document->poster->email);
            }
        }
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
