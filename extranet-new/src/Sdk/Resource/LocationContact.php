<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

class LocationContact
{
    public function __construct(
        public readonly ?string $telephone,
        public readonly ?string $email,
    ) {
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            'telephone' => Type\optional(Type\nullable(Type\string())),
            'sparePartsEmail' => Type\optional(Type\nullable(Type\string())),
            'serviceHubEmail' => Type\optional(Type\nullable(Type\string())),
        ], allowUnknownFields: true);
    }
}
