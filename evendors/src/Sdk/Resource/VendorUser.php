<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * @phpstan-type VendorUserStructure array{"@id": non-empty-string, username: non-empty-string, email: non-empty-string, firstname: non-empty-string, lastname: non-empty-string}
 *
 * @psalm-type VendorUserStructure = array{"@id": non-empty-string, username: non-empty-string, email: non-empty-string, firstname: non-empty-string, lastname: non-empty-string}
 */
final class VendorUser implements ResourceInterface
{
    /**
     * @param non-empty-string $username
     * @param non-empty-string $email
     * @param non-empty-string $firstname
     * @param non-empty-string $lastname
     */
    public function __construct(
        public readonly string $iri,
        public readonly string $username,
        public readonly string $email,
        public readonly string $firstname,
        public readonly string $lastname,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<VendorUserStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'username' => Type\non_empty_string(),
            'email' => Type\non_empty_string(),
            'firstname' => Type\non_empty_string(),
            'lastname' => Type\non_empty_string(),
        ], allow_unknown_fields: true);
    }
}
