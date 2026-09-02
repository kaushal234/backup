<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\DataTransformer\ResourceTransformer\ServiceBulletinFileResourceTransformer;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\ServiceBulletinFile;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class ServiceBulletinFileResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/service_bulletin_files/1',
            '@type' => 'ServiceBulletinFile',
            'id' => 1,
            'filePath' => '/path',
            'description' => 'File description',
            'createdAt' => '2025-07-01',
            'extension' => 'png',
            'size' => 1,
        ]];

        yield [[
            '@id' => '/service_bulletin_files/1',
            '@type' => 'ServiceBulletinFile',
            'id' => 1,
            'filePath' => '/path',
            'description' => null,
            'createdAt' => '2025-07-01',
            'extension' => null,
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[]];

        yield [[
            '@id' => '/service_bulletin_files/1',
            '@type' => 'ServiceBulletinFile',
            // 'id' is missing
            'filePath' => '/path',
            'description' => 'File description',
            'createdAt' => '2025-07-01',
            'extension' => 'png',
            'size' => 1,
        ]];

        yield [[
            '@id' => '/service_bulletin_files/1',
            '@type' => 'ServiceBulletinFile',
            'id' => 1,
            'filePath' => '',
            'description' => null,
            'createdAt' => '2025-07-01',
            'extension' => null,
        ]];

        yield [[
            '@id' => '/service_bulletin_files/1',
            '@type' => 'ServiceBulletinFile',
            'id' => 1,
            'filePath' => '/path',
            'description' => null,
            'createdAt' => '',
            'extension' => null,
        ]];
    }

    protected function getResourceClass(): string
    {
        return ServiceBulletinFile::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new ServiceBulletinFileResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(ServiceBulletinFile::class, $resource);
        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['filePath'], $resource->filePath);
        self::assertSame($structure['description'], $resource->description);
        self::assertSame($structure['createdAt'], $resource->createdAt->format('Y-m-d'));
        self::assertSame($structure['extension'] ?? null, $resource->extension);
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
