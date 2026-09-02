<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

final class LocationAddress
{
    public function __construct(
        public readonly ?string $street = null,
        public readonly ?string $city = null,
        public readonly ?string $country = null,
        public readonly ?string $state = null,
        public readonly ?string $postalCode = null,
    ) {
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            'street1' => Type\optional(Type\nullable(Type\string())),
            'city' => Type\optional(Type\nullable(Type\string())),
            'country' => Type\optional(Type\nullable(Type\string())),
            'state' => Type\optional(Type\nullable(Type\string())),
            'postalCode' => Type\optional(Type\nullable(Type\string())),
        ], allowUnknownFields: true);
    }
}
