<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

final class Currency implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $name,
    ) {
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('Currency'),
            'id' => Type\int(),
            'name' => Type\string(),
        ], allow_unknown_fields: true);
    }
}
