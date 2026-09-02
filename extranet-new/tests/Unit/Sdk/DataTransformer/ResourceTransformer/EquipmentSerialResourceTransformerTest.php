<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\EquipmentSerialResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Resource\EquipmentSerial;
use App\Sdk\Resource\ResourceInterface;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class EquipmentSerialResourceTransformerTest extends AbstractResourceTransformerTest
{
    /**
     * @return iterable<array<array<string, mixed>>>
     */
    public function getCorrectStructures(): iterable
    {
        yield 'with component' => [[
            '@id' => '/equipment_serials/1',
            '@type' => 'equipment_serial',
            'id' => 1,
            'legacyId' => 10,
            'component' => [
                'id' => 100,
                'name' => 'Main boom',
                'signalCode' => 'ESC',
            ],
            'model' => 'TPX-550',
            'serial' => 'T44981',
            'brand' => 'ALVEST',
        ]];

        yield 'without component and nullable fields' => [[
            '@id' => '/equipment_serials/2',
            '@type' => 'equipment_serial',
            'id' => 2,
            'legacyId' => 20,
            'component' => null,
            'model' => null,
            'serial' => null,
            'brand' => null,
        ]];
    }

    /**
     * @return iterable<array<array<string, mixed>>>
     */
    public function getIncorrectStructures(): iterable
    {
        yield 'missing id' => [[
            '@id' => '/equipment_serials/1',
            '@type' => 'equipment_serial',
            'legacyId' => 10,
            'component' => null,
            'model' => 'TPX-550',
            'serial' => 'T44981',
            'brand' => 'ALVEST',
        ]];

        yield 'invalid component structure' => [[
            '@id' => '/equipment_serials/1',
            '@type' => 'equipment_serial',
            'id' => 1,
            'legacyId' => 10,
            'component' => [
                'id' => 'not_an_int',
                'name' => 'Main boom',
                'signalCode' => 'ESC',
            ],
            'model' => 'TPX-550',
            'serial' => 'T44981',
            'brand' => 'ALVEST',
        ]];

        yield 'empty payload' => [[]];

        yield 'unrelated payload' => [[
            'foo' => 'bar',
            'baz' => 123,
        ]];
    }

    public function testTransformPageThrowsFailedTransformationException(): void
    {
        $this->expectException(FailedTransformationException::class);

        $transformer = $this->getResourceTransformer();

        $transformer->transformPage(
            data: [
                'hydra:member' => [],
                'hydra:totalItems' => 0,
                'hydra:view' => [],
            ],
            page: 1,
            itemsPerPage: 10,
        );
    }

    protected function getResourceClass(): string
    {
        return EquipmentSerial::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new EquipmentSerialResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(EquipmentSerial::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['legacyId'], $resource->legacyId);

        self::assertSame($structure['model'] ?? null, $resource->model);
        self::assertSame($structure['serial'] ?? null, $resource->serial);
        self::assertSame($structure['brand'] ?? null, $resource->brand);

        // Component nullable
        $componentStructure = $structure['component'] ?? null;
        if (null === $componentStructure) {
            self::assertNull($resource->component);
        } else {
            self::assertNotNull($resource->component);
            self::assertSame($componentStructure['id'], $resource->component->id);
            self::assertSame($componentStructure['name'], $resource->component->name);
            self::assertSame($componentStructure['signalCode'] ?? null, $resource->component->signalCode);
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
