<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\CommentResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\Comment;
use App\Sdk\Resource\ResourceInterface;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class CommentResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/comments/42',
            '@type' => 'Comment',
            'id' => 42,
            'message' => 'test message',
            'user' => null,
            'createdAt' => '2025-06-01',
        ]];

        yield [[
            '@id' => '/comments/42',
            '@type' => 'Comment',
            'id' => 42,
            'message' => 'test message',
            'user' => [
                '@id' => '/people/69',
                '@type' => 'People',
                'id' => 69,
                'lastname' => 'Doe',
                'firstname' => 'John',
                'email' => 'john.doe@exemple.com',
            ],
            'createdAt' => '2025-06-01',
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@type' => 'Comment',
            'id' => 13,
        ]];

        yield [[
            '@type' => 'foo',
            '@id' => 13,
            'message' => 'Kinder',
        ]];

        yield [[]];

        yield [[
            'message' => 'some message',
        ]];
    }

    protected function getResourceClass(): string
    {
        return Comment::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new CommentResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(Comment::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['message'], $resource->message);
        self::assertSame($structure['createdAt'], $resource->createdAt->format('Y-m-d'));
        self::assertSame($structure['user']['@id'] ?? null, $resource->user?->iri);
        self::assertSame($structure['user']['id'] ?? null, $resource->user?->id);
        self::assertSame($structure['user']['lastname'] ?? null, $resource->user?->lastname);
        self::assertSame($structure['user']['firstname'] ?? null, $resource->user?->firstname);
        self::assertSame($structure['user']['email'] ?? null, $resource->user?->email);
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
