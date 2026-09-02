<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-type CommentFileStructure array{"@id": non-empty-string, "@type": "CommentFile", "id": positive-int, "filePath": non-empty-string, createdAt: non-empty-string}
 *
 * @psalm-type CommentFileStructure = array{"@id": non-empty-string, "@type": "CommentFile", "id": positive-int, "filePath": non-empty-string, createdAt: non-empty-string}
 */
final class CommentFile implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $filePath,
        public readonly string $createdAt,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<CommentFileStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('CommentFile'),
            'id' => Type\positive_int(),
            'filePath' => Type\non_empty_string(),
            'createdAt' => Type\non_empty_string(),
        ], allow_unknown_fields: true);
    }
}
