<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

class People implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $lastname,
        public readonly string $firstname,
        public readonly string $email,
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
            'lastname' => Type\non_empty_string(),
            'firstname' => Type\non_empty_string(),
            'email' => Type\non_empty_string(),
        ], allowUnknownFields: true);
    }
}
