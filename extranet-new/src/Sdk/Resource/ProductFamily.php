<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

final class ProductFamily implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $name,
        public readonly ?ProductType $productType = null,
        public readonly ?string $englishDescription = null,
        public readonly ?string $frenchDescription = null,
        public readonly ?string $chineseDescription = null,
        public readonly ?int $dms = null,
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
            'productType' => Type\nullable(ProductType::getSimplifiedStructure()),
            'englishDescription' => Type\nullable(Type\non_empty_string()),
            'frenchDescription' => Type\nullable(Type\string()),
            'chineseDescription' => Type\nullable(Type\string()),
            'dmsPhoto' => Type\nullable(Type\int()),
        ], allowUnknownFields: true);
    }

    public static function getSimplifiedTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'id' => Type\int(),
            'name' => Type\non_empty_string(),
            'dmsPhoto' => Type\nullable(Type\int()),
        ], allowUnknownFields: true);
    }
}
