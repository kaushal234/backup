<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

final class Airport implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $code,
        public readonly ?string $city = null,
        public readonly ?string $name = null,
        public readonly ?Country $country = null,
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
            'code' => Type\non_empty_string(),
            'cityName' => Type\optional(Type\nullable(Type\non_empty_string())),
            'name' => Type\optional(Type\nullable(Type\non_empty_string())),
            'country' => Type\optional(Type\nullable(Country::getTypeStructure())),
        ], allowUnknownFields: true);
    }
}
