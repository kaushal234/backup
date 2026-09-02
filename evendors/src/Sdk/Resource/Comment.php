<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * @phpstan-import-type PersonStructure from Person
 *
 * @psalm-import-type PersonStructure from Person
 *
 * @phpstan-import-type CommentFileStructure from CommentFile
 *
 * @psalm-import-type CommentFileStructure from CommentFile
 *
 * @phpstan-type CommentStructure array{"@id": non-empty-string, "@type": non-empty-string, message: non-empty-string, resource: non-empty-string, user: ?PersonStructure, createdAt: non-empty-string, updatedAt: non-empty-string, public: bool, files: list<CommentFileStructure>, metadata: list<string|int>}
 *
 * @psalm-type CommentStructure = array{"@id": non-empty-string, "@type": non-empty-string, message: non-empty-string, resource: non-empty-string, user: ?PersonStructure, createdAt: non-empty-string, updatedAt: non-empty-string, public: bool, files: list<CommentFileStructure>, metadata: list<string|int>}
 */
final class Comment implements ActivityInterface
{
    /**
     * @param array<mixed>      $metadata
     * @param list<CommentFile> $files
     */
    public function __construct(
        public readonly string $iri,
        public readonly string $resource,
        public readonly ?string $message,
        public readonly ?Person $user,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly bool $public,
        public readonly array $metadata = [],
        public readonly array $files = [],
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<CommentStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('Comment'),
            'resource' => Type\non_empty_string(),
            'message' => Type\optional(Type\string()),
            'user' => Type\nullable(Person::getTypeStructure()),
            'createdAt' => Type\non_empty_string(),
            'updatedAt' => Type\non_empty_string(),
            'public' => Type\bool(),
            'files' => Type\optional(Type\vec(CommentFile::getTypeStructure())),
        ], allow_unknown_fields: true);
    }

    public function getType(): string
    {
        return ActivityInterface::COMMENT_TYPE;
    }
}
