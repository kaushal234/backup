<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-import-type EvendorsNewsFileStructure from EvendorsNewsFile
 *
 * @psalm-import-type EvendorsNewsFileStructure from EvendorsNewsFile
 *
 * @phpstan-type EvendorsNewsStructure array{"@id": non-empty-string, "id": positive-int, content: non-empty-string, publishedAt: non-empty-string, "files": null|list<EvendorsNewsFileStructure>}
 *
 * @psalm-type EvendorsNewsStructure = array{"@id": non-empty-string, "id": positive-int, content: non-empty-string, publishedAt: non-empty-string, "files": null|list<EvendorsNewsFileStructure>}
 */
final class EvendorsNews implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param list<EvendorsNewsFile> $files
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $content,
        public readonly string $publishedAt,
        public readonly array $files = [],
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<EvendorsNewsStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'id' => Type\int(),
            'content' => Type\non_empty_string(),
            'publishedAt' => Type\string(),
            'files' => Type\optional(Type\vec(EvendorsNewsFile::getTypeStructure())),
        ], allow_unknown_fields: true);
    }
}
