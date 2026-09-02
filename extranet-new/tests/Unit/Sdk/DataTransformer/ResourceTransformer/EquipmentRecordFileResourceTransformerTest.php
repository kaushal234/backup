<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\EquipmentRecordFileResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\EquipmentRecordFile;
use App\Sdk\Resource\ResourceInterface;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class EquipmentRecordFileResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/legacy/equipment_record_files/1',
            '@type' => 'EquipmentRecordFile',
            'id' => 1,
            'parentId' => 37454,
            'displayFilename' => 'customer_manual.pdf',
            'description' => 'Customer manual',
            'date' => '2024-01-15',
        ]];

        yield [[
            '@id' => '/legacy/equipment_record_files/2',
            '@type' => 'EquipmentRecordFile',
            'id' => 2,
            'parentId' => 37454,
            'displayFilename' => 'no_description.pdf',
            'description' => null,
            'date' => null,
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[]];

        yield [[
            '@id' => '/legacy/equipment_record_files/1',
            '@type' => 'EquipmentRecordFile',
            // 'id' is missing
            'displayFilename' => 'customer_manual.pdf',
            'description' => 'Customer manual',
            'date' => '2024-01-15',
        ]];

        yield [[
            '@id' => '/legacy/equipment_record_files/1',
            '@type' => 'EquipmentRecordFile',
            'id' => 1,
            'displayFilename' => '',
            'description' => null,
            'date' => null,
        ]];
    }

    protected function getResourceClass(): string
    {
        return EquipmentRecordFile::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new EquipmentRecordFileResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(EquipmentRecordFile::class, $resource);
        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['displayFilename'], $resource->displayFilename);
        self::assertSame($structure['description'], $resource->description);
        self::assertSame($structure['date'] ?? null, $resource->date?->format('Y-m-d'));
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
