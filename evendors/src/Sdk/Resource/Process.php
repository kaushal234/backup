<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

final class Process implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly string $category,
        public readonly string $description,
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
            '@type' => Type\literal_scalar('Process'),
            'category' => Type\string(),
            'description' => Type\string(),
        ], allow_unknown_fields: true);
    }
}
