<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

class EmissionRating implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $name,
        public readonly bool $obsolete,
        public readonly int $legacyId,
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
            'name' => Type\non_empty_string(),
            'obsolete' => Type\bool(),
        ], allowUnknownFields: true);
    }
}
