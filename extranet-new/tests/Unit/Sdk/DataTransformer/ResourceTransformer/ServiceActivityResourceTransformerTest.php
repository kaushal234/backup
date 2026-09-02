<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\DataTransformer\ResourceTransformer\ServiceActivityResourceTransformer;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\ServiceActivity;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class ServiceActivityResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/service_activity/1',
            'id' => 1,
            '@type' => 'ServiceActivity',
            'name' => 'test service_activity',
            'description' => 'test description service_activity',
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@id' => '/service_activity/1',
            'id' => 1,
            '@type' => 'ServiceActivity',
            'name' => 'test service_activity',
        ]];

        yield [[]];
    }

    protected function getResourceClass(): string
    {
        return ServiceActivity::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new ServiceActivityResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(ServiceActivity::class, $resource);

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
