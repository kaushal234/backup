<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\TypeStructureTrait;
use Psl\Type;

final class Product implements ResourceInterface
{
    use TypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $name,
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
            'name' => Type\non_empty_string(),
        ], allowUnknownFields: true);
    }

    public static function getCollectionTypeStructure(): Type\TypeInterface
    {
        return self::getCollectionTypeStructureOf(self::getTypeStructure());
    }

    public static function getPageTypeStructure(): Type\TypeInterface
    {
        return self::getPageTypeStructureOf(self::getTypeStructure());
    }
}
