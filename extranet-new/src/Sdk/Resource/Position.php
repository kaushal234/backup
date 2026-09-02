<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

class Position implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly string $name,
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
            'name' => Type\non_empty_string(),
        ], allowUnknownFields: true);
    }
}
