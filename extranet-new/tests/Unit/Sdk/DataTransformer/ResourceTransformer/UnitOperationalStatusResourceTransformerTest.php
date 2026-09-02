<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\DataTransformer\ResourceTransformer\UnitOperationalStatusResourceTransformer;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\UnitOperationalStatus;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class UnitOperationalStatusResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/unit_operational_status/1',
            'id' => 1,
            '@type' => 'UnitOperationalStatus',
            'name' => 'test unit_operational_status',
            'description' => 'test description unit_operational_status',
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@id' => '/unit_operational_status/1',
            'id' => 1,
            '@type' => 'UnitOperationalStatus',
            'name' => 'test unit_operational_status',
        ]];

        yield [[]];
    }

    protected function getResourceClass(): string
    {
        return UnitOperationalStatus::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new UnitOperationalStatusResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(UnitOperationalStatus::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['name'], $resource->name);
        self::assertSame($structure['description'], $resource->description);
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
