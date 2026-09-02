<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * ResourceInterface used to download ManualDocument files
 * via the ManualDocument API endpoint.
 */
final class ManualDocumentPublic implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly ?string $description = null,
        public readonly ?string $categoryName = null,
        public readonly ?int $fileId = null,
        public readonly ?string $extension = null,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@type' => Type\non_empty_string(),
            '@id' => Type\non_empty_string(),
            'id' => Type\int(),
            'description' => Type\nullable(Type\non_empty_string()),
            'categoryName' => Type\nullable(Type\non_empty_string()),
            'fileId' => Type\nullable(Type\int()),
            'extension' => Type\nullable(Type\non_empty_string()),
        ]);
    }
}
