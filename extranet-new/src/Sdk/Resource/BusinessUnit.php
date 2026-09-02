<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

final class BusinessUnit implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly string $name,
        public readonly Location $location,
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
            'location' => Location::getTypeStructure(),
        ], allowUnknownFields: true);
    }
}
