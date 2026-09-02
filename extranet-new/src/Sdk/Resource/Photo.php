<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

class Photo implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $filePath,
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
            'filePath' => Type\non_empty_string(),
        ], allowUnknownFields: true);
    }
}
