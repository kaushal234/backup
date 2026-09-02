<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ManualDocumentResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\ManualDocument;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Utils\IriToId;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class ManualDocumentResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
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
            'manual' => [
                '@id' => '/manual/1',
                'equipmentRecord' => [
                    '@id' => '/equipments/1',
                    'id' => 1,
                    'serialNumber' => 'T1000',
                    'model' => 'T-MAX',
                    'legacyId' => 12,
                ],
            ],
            'parts' => [
                [
                    '@id' => '/parts/1',
                    'partNumber' => '123456',
                    'quantity' => 2,
                    'position' => 1,
                    'unitOfMeasure' => 'unit',
                    'description' => 'this is a description',
                    'otherDescription' => 'this is another description',
                    'preventive' => true,
                    'maintenance' => true,
                    'overhaul' => true,
                    'critical' => true,
                ],
            ],
        ]];

        yield [[
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
            'document' => null,
            'manual' => [
                '@id' => '/manual/1',
                'equipmentRecord' => [
                    '@id' => '/equipments/1',
                    'id' => 1,
                    'serialNumber' => 'T1000',
                    'model' => 'T-MAX',
                    'legacyId' => 12,
                ],
            ],
            'parts' => [],
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@type' => 'manual_documents',
            'id' => 13,
        ]];

        yield [[
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
            'document' => [],
            'manual' => null,
            'parts' => [],
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
        return ManualDocument::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new ManualDocumentResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(ManualDocument::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame(IriToId::iriToId($structure['@id']), $resource->id);
        self::assertSame($structure['position'], $resource->position);
        self::assertSame($structure['revision'], $resource->revision);
        self::assertSame($structure['description'], $resource->description);
        self::assertSame($structure['otherDescription'], $resource->otherDescription);
        self::assertSame($structure['type'], $resource->type);
        self::assertSame($structure['factoryNumber'], $resource->factoryNumber);

        if (null !== $structure['document']) {
            self::assertSame($structure['document']['@id'], $resource->document->iri);
            self::assertSame($structure['document']['id'], $resource->document->id);
            self::assertSame($structure['document']['filePath'], $resource->document->filePath);
            self::assertSame($structure['document']['description'], $resource->document->description);
            self::assertSame($structure['document']['createdAt'], $resource->document->createdAt->format('Y-m-d'));
            self::assertSame($structure['document']['extension'], $resource->document->extension);
            self::assertSame($structure['document']['poster']['@id'], $resource->document->poster?->iri);
            self::assertSame($structure['document']['poster']['lastname'], $resource->document->poster?->lastname);
            self::assertSame($structure['document']['poster']['firstname'], $resource->document->poster?->firstname);
            self::assertSame($structure['document']['poster']['email'], $resource->document->poster?->email);
        }

        self::assertSame($structure['manual']['@id'], $resource->manual->iri);
        self::assertSame($structure['manual']['equipmentRecord']['@id'], $resource->manual->equipment->iri);
        self::assertSame($structure['manual']['equipmentRecord']['id'], $resource->manual->equipment->id);
        self::assertSame($structure['manual']['equipmentRecord']['serialNumber'], $resource->manual->equipment->serialNumber);
        self::assertSame($structure['manual']['equipmentRecord']['model'], $resource->manual->equipment->product);
        self::assertSame($structure['manual']['equipmentRecord']['legacyId'], $resource->manual->equipment->legacyId);
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
