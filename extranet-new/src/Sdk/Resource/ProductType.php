<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

final class ProductType implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param array<ProductFamily> $productFamilies
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $englishName,
        public readonly ?string $frenchName = null,
        public readonly ?string $chineseName = null,
        public readonly ?Document $dms = null,
        public readonly array $productFamilies = [],
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
            'englishName' => Type\non_empty_string(),
            'frenchName' => Type\nullable(Type\non_empty_string()),
            'chineseName' => Type\nullable(Type\non_empty_string()),
            'dms' => Type\nullable(Document::getTypeStructure()),
            'productFamilies' => Type\optional(Type\vec(ProductFamily::getSimplifiedTypeStructure())),
        ], allowUnknownFields: true);
    }

    public static function getSimplifiedStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'id' => Type\int(),
            'englishName' => Type\non_empty_string(),
            'frenchName' => Type\nullable(Type\non_empty_string()),
            'chineseName' => Type\nullable(Type\non_empty_string()),
        ], allowUnknownFields: true);
    }
}
