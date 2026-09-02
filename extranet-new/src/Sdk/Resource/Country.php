<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

final class Country implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $isoCode2 = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->iri;
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
            'name' => Type\non_empty_string(),
            'isoCode2' => Type\optional(Type\nullable(Type\non_empty_string())),
        ], allowUnknownFields: true);
    }
}
