<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\DataTransformer\ResourceTransformer\ServiceBulletinResourceTransformer;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\ServiceBulletin;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class ServiceBulletinResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/service_bulletins/1',
            '@type' => 'ServiceBulletin',
            'id' => 1,
            'title' => 'SB-001',
            'description' => 'Initial release of service bulletin.',
            'type' => 'maintenance',
            'confidential' => 'no',
            'createdAt' => '2025-06-01',
            'category' => 'INFORMATION',
            'status' => 'PARTIAL_IMPLEMENTATION',
            'ssdDecidedAt' => '2025-06-01',
            'partsNeeded' => true,
        ]];

        yield [[
            '@id' => '/service_bulletins/2',
            '@type' => 'ServiceBulletin',
            'id' => 2,
            'title' => 'SB-002',
            'description' => null,
            'type' => null,
            'confidential' => 'yes',
            'createdAt' => '2025-07-01',
            'category' => null,
            'status' => null,
            'ssdDecidedAt' => null,
            'partsNeeded' => false,
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[]];

        yield [[
            '@id' => '/service_bulletins/3',
            '@type' => 'ServiceBulletin',
            // 'id' is missing
            'title' => 'Missing ID',
            'confidential' => 'yes',
            'createdAt' => '2025-07-01',
        ]];

        yield [[
            '@id' => '/service_bulletins/4',
            '@type' => 'ServiceBulletin',
            'id' => 4,
            'title' => '',
            'confidential' => 'yes',
            'createdAt' => '2025-07-01',
        ]];

        yield [[
            '@id' => '/service_bulletins/5',
            '@type' => 'ServiceBulletin',
            'id' => 5,
            'title' => 'SB-005',
            'confidential' => 'yes',
            'createdAt' => null,
        ]];
    }

    protected function getResourceClass(): string
    {
        return ServiceBulletin::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new ServiceBulletinResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(ServiceBulletin::class, $resource);
        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['title'], $resource->title);
        self::assertSame($structure['confidential'], $resource->confidential);
        self::assertSame($structure['createdAt'], $resource->createdAt);
        self::assertSame($structure['description'] ?? null, $resource->description);
        self::assertSame($structure['type'] ?? null, $resource->type);
        self::assertSame($structure['category'] ?? null, $resource->category);
        self::assertSame($structure['status'] ?? null, $resource->status);
        self::assertSame($structure['ssdDecidedAt'] ?? null, $resource->ssdDecidedAt);
        self::assertSame($structure['partsNeeded'] ?? null, $resource->partsNeeded);
    }

    protected function supportsCollections(): bool
    {
        return false;
    }

    protected function supportsPage(): bool
    {
        return true;
    }
}
