<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\DocumentResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\Document;
use App\Sdk\Resource\ResourceInterface;

/**
 * @group unit
 */
final class DocumentResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/dms/1',
            '@type' => 'dms',
            'id' => 13,
            'legacyId' => 130,
            'title' => 'some_title',
            'subject' => 'some_subject',
            'description' => 'some_description',
            'type' => 'some_type',
            'language' => 'en',
            'portal' => 'some_portal',
            'owner' => [
                '@id' => '/person/3',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
        ]];

        yield [[
            '@id' => '/dms/1',
            '@type' => 'dms',
            'id' => 13,
            'legacyId' => 130,
            'title' => 'some_title',
            'subject' => 'some_subject',
            'description' => 'some_description',
            'type' => null,
            'language' => 'en',
            'portal' => '',
            'owner' => [
                '@id' => '/person/3',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@type' => 'dms',
            'id' => 13,
            'title' => 'some_title',
            'subject' => 'some_subject',
            'description' => 'some_description',
            'type' => 'some_type',
            'language' => 'en',
            'portal' => 'some_portal',
            'owner' => [
                'username' => '',
                'email' => '',
                'firstname' => '',
                'lastname' => '',
            ],
        ]];

        yield [[
            '@type' => 'foo',
            'id' => 13,
            'title' => 'some_title',
            'subject' => 'some_subject',
            'description' => 'some_description',
            'type' => null,
            'language' => 'en',
            'portal' => '',
            'owner' => [
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
        ]];

        yield [[
            '@type' => 'dms',
            'id' => -13,
            'title' => 'some_title',
            'subject' => 'some_subject',
            'description' => 'some_description',
            'type' => null,
            'language' => 'en',
            'portal' => '',
            'owner' => [
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
        ]];

        yield [[
            '@type' => 'dms',
            'id' => 13,
            'title' => 'some_title',
            'subject' => 'some_subject',
            'description' => 'some_description',
            'type' => null,
            'language' => 'en',
            'portal' => '',
            'owner' => [],
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
        return Document::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new DocumentResourceTransformer();
    }

    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(Document::class, $resource);

        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['title'], $resource->title);
        self::assertSame($structure['subject'], $resource->subject);
        self::assertSame($structure['description'], $resource->description);
        self::assertSame($structure['type'], $resource->type);
        self::assertSame($structure['language'], $resource->language);
        self::assertSame($structure['portal'], $resource->portal);
        self::assertSame($structure['owner']['username'], $resource->owner->username);
        self::assertSame($structure['owner']['email'], $resource->owner->email);
        self::assertSame($structure['owner']['firstname'], $resource->owner->firstname);
        self::assertSame($structure['owner']['lastname'], $resource->owner->lastname);
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
