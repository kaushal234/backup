<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

final class Document implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly int $legacyId,
        public readonly string $type,
        public readonly string $title,
        public readonly string $description,
        public readonly string $language,
        public readonly string $createdAt
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'id' => Type\int(),
            'legacyId' => Type\int(),
            'title' => Type\non_empty_string(),
            'description' => Type\non_empty_string(),
            'type' => Type\non_empty_string(),
            'language' => Type\non_empty_string(),
            'createdAt' => Type\non_empty_string(),
        ], allowUnknownFields: true);
    }
}
