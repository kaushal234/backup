<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\DataTransformer\ResourceTransformer\TechnicianOnCallTypeResourceTransformer;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\TechnicianOnCallType;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class TechnicianOnCallTypeResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/technician_on_call_type/1',
            'id' => 1,
            '@type' => 'TechnicianOnCallType',
            'name' => 'test technician_on_call_type',
            'description' => 'test description technician_on_call_type',
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@id' => '/technician_on_call_type/1',
            'id' => 1,
            '@type' => 'TechnicianOnCallType',
            'name' => 'test technician_on_call_type',
        ]];

        yield [[]];
    }

    protected function getResourceClass(): string
    {
        return TechnicianOnCallType::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new TechnicianOnCallTypeResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(TechnicianOnCallType::class, $resource);

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
