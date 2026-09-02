<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * @phpstan-type PersonStructure array{"@id": non-empty-string, username: non-empty-string, email: non-empty-string, firstname: string, lastname: string}
 *
 * @psalm-type PersonStructure = array{"@id": non-empty-string, username: non-empty-string, email: non-empty-string, firstname: string, lastname: string}
 */
final class Person implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly string $username,
        public readonly string $email,
        public readonly ?string $firstname,
        public readonly ?string $lastname,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<PersonStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'username' => Type\non_empty_string(),
            'email' => Type\non_empty_string(),
            'firstname' => Type\nullable(Type\non_empty_string()),
            'lastname' => Type\nullable(Type\string()),
        ], allow_unknown_fields: true);
    }
}
