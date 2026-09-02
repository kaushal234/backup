<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\DatasheetResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\Datasheet;
use App\Sdk\Resource\ResourceInterface;

/**
 * @group unit
 */
final class DocumentResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/datasheet/1',
            'id' => 1,
            '@type' => 'datasheet',
            'dms' => [
                '@id' => '/dms/1',
                '@type' => 'dms',
                'legacyId' => 13,
                'id' => 13,
                'title' => 'some_title',
                'description' => 'some_description',
                'type' => 'some_type',
                'language' => 'en',
                'createdAt' => '2024-01-01',
                'foo' => 'bar',
            ],
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@id' => '/datasheet/1',
            '@type' => 'datasheet',
            'dms' => [
                '@id' => '/dms/1',
                '@type' => 'dms',
                'legacyId' => 13,
                'title' => 'some_title',
                'description' => 'some_description',
                'type' => 'some_type',
                'language' => 'en',
                'createdAt' => '',
            ],
        ]];

        yield [[
            '@id' => '/datasheet/1',
            'id' => 1,
            '@type' => 'datasheet',
            'dms' => [
                '@id' => '/dms/1',
                '@type' => 'dms',
                'legacyId' => 13,
                'title' => 'some_title',
                'description' => 'some_description',
                'type' => 'some_type',
                'language' => '',
                'createdAt' => '2024-01-01',
            ],
        ]];

        yield [[
            '@id' => '/datasheet/1',
            'id' => 1,
            '@type' => 'datasheet',
            'dms' => [
                '@id' => '/dms/1',
                '@type' => 'dms',
                'legacyId' => 13,
                'title' => 'some_title',
                'description' => 'some_description',
                'type' => '',
                'language' => 'en',
                'createdAt' => '2024-01-01',
            ],
        ]];

        yield [[
            '@id' => '/datasheet/1',
            'id' => 1,
            '@type' => 'datasheet',
            'dms' => [
                '@id' => '/dms/1',
                '@type' => 'dms',
                'legacyId' => 13,
                'title' => 'some_title',
                'description' => '',
                'type' => 'some_type',
                'language' => 'en',
                'createdAt' => '2024-01-01',
            ],
        ]];

        yield [[
            '@id' => '/datasheet/1',
            'id' => 1,
            '@type' => 'datasheet',
            'dms' => [
                '@id' => '/dms/1',
                '@type' => 'dms',
                'legacyId' => 13,
                'title' => '',
                'description' => 'some_description',
                'type' => 'some_type',
                'language' => 'en',
                'createdAt' => '2024-01-01',
            ],
        ]];

        yield [[
            '@id' => '/datasheet/1',
            'id' => 1,
            '@type' => 'datasheet',
            'dms' => [
                '@id' => '/dms/1',
                '@type' => 'dms',
                'legacyId' => '13',
                'title' => 'some_title',
                'description' => 'some_description',
                'type' => 'some_type',
                'language' => 'en',
                'createdAt' => '2024-01-01',
            ],
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
        return Datasheet::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new DatasheetResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(Datasheet::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['dms']['@id'], $resource->document->iri);
        self::assertSame($structure['dms']['id'], $resource->document->id);
        self::assertSame($structure['dms']['legacyId'], $resource->document->legacyId);
        self::assertSame($structure['dms']['title'], $resource->document->title);
        self::assertSame($structure['dms']['description'], $resource->document->description);
        self::assertSame($structure['dms']['type'], $resource->document->type);
        self::assertSame($structure['dms']['language'], $resource->document->language);
        self::assertSame($structure['dms']['createdAt'], $resource->document->createdAt);
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
