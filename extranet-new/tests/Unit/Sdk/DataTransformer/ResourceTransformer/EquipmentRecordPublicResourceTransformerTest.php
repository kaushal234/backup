<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\EquipmentRecordPublicResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\EquipmentRecordPublic;
use App\Sdk\Resource\ManualDocumentPublic;
use App\Sdk\Resource\ManualPublic;
use App\Sdk\Resource\ResourceInterface;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class EquipmentRecordPublicResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@context' => '/contexts/EquipmentRecordPublic',
            '@id' => '/public/equipment_records/T116605',
            '@type' => 'EquipmentRecordPublic',
            'id' => 112994,
            'serialNumber' => 'T116605',
            'model' => 'NBL-E',
            'type' => 'Belt Loaders',
            'airportCode' => null,
            'optionsDescription' => '<p>BASE UNIT...</p>',
            'lastManual' => [
                '@type' => 'ManualPublic',
                '@id' => '/.well-known/genid/whatever-1',
                'id' => 99170,
                'createdAt' => '2025-12-30T05:47:58-05:00',
                'documents' => [
                    [
                        '@type' => 'ManualDocumentPublic',
                        '@id' => '/.well-known/genid/doc-1',
                        'id' => 8159802,
                        'description' => 'CH0,INFORMATION [EN]',
                        'categoryName' => 'Chapter 0',
                        'fileId' => 8837415,
                        'extension' => 'pdf',
                    ],
                    [
                        '@type' => 'ManualDocumentPublic',
                        '@id' => '/.well-known/genid/doc-2',
                        'id' => 8159804,
                        'description' => 'CH1,OPERATING,NBL-E MK2 [EN]',
                        'categoryName' => 'Chapter 1',
                        'fileId' => 8837417,
                        'extension' => 'pdf',
                    ],
                ],
            ],
        ]];

        yield [[
            '@context' => '/contexts/EquipmentRecordPublic',
            '@id' => '/public/equipment_records/T101010',
            '@type' => 'EquipmentRecordPublic',
            'id' => 42,
            'serialNumber' => 'T101010',
            'model' => 'TPX',
            'type' => null,
            'airportCode' => 'CDG',
            'optionsDescription' => null,
            'lastManual' => null,
        ]];

        yield [[
            '@context' => '/contexts/EquipmentRecordPublic',
            '@id' => '/public/equipment_records/ABC123',
            '@type' => 'EquipmentRecordPublic',
            'id' => 1337,
            'serialNumber' => 'ABC123',
            'model' => 'NBL',
            'type' => 'Belt Loaders',
            'airportCode' => null,
            'optionsDescription' => 'Some options',
            'lastManual' => [
                '@type' => 'ManualPublic',
                '@id' => '/.well-known/genid/whatever-1',
                'id' => 99170,
                'createdAt' => '2025-12-30T05:47:58-05:00',
                'documents' => [],
            ],
        ]];
    }

    /**
     * The collection projection has no `lastManual` key at all: it must still be supported
     * and transform into a resource whose `lastManual` is null.
     */
    public function testTransformsCollectionMemberWithoutLastManual(): void
    {
        $member = [
            '@id' => '/public/equipment_records/T222222',
            '@type' => 'EquipmentRecordPublicListItem',
            'id' => 777,
            'serialNumber' => 'T222222',
            'model' => 'TPX',
            'type' => 'Belt Loaders',
            'airportCode' => 'CDG',
            'optionsDescription' => null,
        ];

        $transformer = $this->getResourceTransformer();
        $data = ['@type' => 'hydra:Collection', 'hydra:member' => [$member]];

        self::assertTrue($transformer->supportsCollection(EquipmentRecordPublic::class, $data));

        $collection = $transformer->transformCollection($data);

        self::assertCount(1, $collection);
        self::assertNull($collection->at(0)->lastManual);
        self::assertSame('T222222', $collection->at(0)->serialNumber);
    }

    public function getIncorrectStructures(): iterable
    {
        // Missing required id
        yield [[
            'serialNumber' => 'T116605',
            'model' => 'NBL-E',
            'type' => 'Belt Loaders',
            'airportCode' => null,
        ]];

        // Missing required serialNumber
        yield [[
            'id' => 112994,
            'model' => 'NBL-E',
            'type' => 'Belt Loaders',
            'airportCode' => null,
        ]];

        // Invalid id type
        yield [[
            'id' => 'not-an-int',
            'serialNumber' => 'T116605',
            'model' => 'NBL-E',
            'type' => 'Belt Loaders',
            'airportCode' => null,
        ]];

        // Invalid lastManual structure (missing required id)
        yield [[
            'id' => 112994,
            'serialNumber' => 'T116605',
            'model' => 'NBL-E',
            'type' => 'Belt Loaders',
            'airportCode' => null,
            'lastManual' => [
                'createdAt' => '2025-12-30T05:47:58-05:00',
                'documents' => [],
            ],
        ]];

        // Completely unrelated payload
        yield [[
            'username' => 'some_username',
            'email' => 'some_email',
        ]];

        // Empty payload
        yield [[]];
    }

    protected function getResourceClass(): string
    {
        return EquipmentRecordPublic::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new EquipmentRecordPublicResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(
        ResourceInterface $resource,
        array $structure,
        bool $fromCollectionOrPage
    ): void {
        self::assertInstanceOf(EquipmentRecordPublic::class, $resource);

        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['serialNumber'], $resource->serialNumber);
        self::assertSame($structure['model'], $resource->product);
        self::assertSame($structure['type'], $resource->productType ?? null);
        self::assertSame($structure['airportCode'] ?? null, $resource->airportCode);
        self::assertSame($structure['optionsDescription'] ?? null, $resource->optionsDescription);

        // lastManual assertions
        if (!\array_key_exists('lastManual', $structure) || null === $structure['lastManual']) {
            self::assertNull($resource->lastManual);

            return;
        }

        self::assertInstanceOf(ManualPublic::class, $resource->lastManual);

        $manualStructure = $structure['lastManual'];
        $manualResource = $resource->lastManual;

        self::assertSame($manualStructure['id'], $manualResource->id);
        self::assertSame($manualStructure['createdAt'], $manualResource->createdAt);

        // Manual documents assertions
        $documentStructures = $manualStructure['documents'] ?? [];
        $documentResources = $manualResource->documents;

        self::assertCount(\count($documentStructures), $documentResources);

        foreach ($documentStructures as $index => $documentStructure) {
            /** @var ManualDocumentPublic $documentResource */
            $documentResource = $documentResources[$index];

            self::assertSame($documentStructure['id'], $documentResource->id);
            self::assertSame($documentStructure['description'], $documentResource->description);
            self::assertSame($documentStructure['categoryName'], $documentResource->categoryName);
            self::assertSame($documentStructure['fileId'], $documentResource->fileId);
            self::assertSame($documentStructure['extension'], $documentResource->extension);
        }
    }

    protected function supportsCollections(): bool
    {
        return true;
    }

    protected function supportsPage(): bool
    {
        return true;
    }
}
