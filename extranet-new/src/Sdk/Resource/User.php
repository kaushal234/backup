<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

class User implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $profileIri,
        public readonly string $lastname,
        public readonly string $firstname,
        public readonly string $email,
        public readonly ?string $title = null,
        public readonly ?string $division = null,
        public readonly ?string $department = null,
        public readonly ?string $language = null,
        public readonly ?UserAddress $address = null,
        public readonly ?UserContact $contact = null,
        public readonly ?string $passwordExpirationDate = null,
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
            'lastname' => Type\string(),
            'firstname' => Type\string(),
            'email' => Type\string(),
            'extranetUserProfile' => Type\shape([
                '@id' => Type\non_empty_string(),
                'jobTitle' => Type\nullable(Type\string()),
                'division' => Type\nullable(Type\string()),
                'department' => Type\nullable(Type\string()),
                'language' => Type\nullable(Type\string()),
                'country' => Type\nullable(Country::getTypeStructure()),
            ], allowUnknownFields: true),
            'address' => Type\shape([
                'street1' => Type\nullable(Type\string()),
                'street2' => Type\nullable(Type\string()),
                'city' => Type\nullable(Type\string()),
                'state' => Type\nullable(Type\string()),
                'postalCode' => Type\nullable(Type\string()),
            ], allowUnknownFields: true),
        ], allowUnknownFields: true);
    }
}
