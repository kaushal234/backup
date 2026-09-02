<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

final class EquipmentRecordPublic implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $serialNumber,
        public readonly string $product,
        public readonly ?string $productType,
        public readonly ?string $airportCode,
        public readonly ?string $optionsDescription,
        public readonly ?ManualPublic $lastManual = null,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@context' => Type\optional(Type\non_empty_string()),
            '@id' => Type\non_empty_string(),
            '@type' => Type\non_empty_string(),
            'id' => Type\int(),
            'serialNumber' => Type\non_empty_string(),
            'model' => Type\non_empty_string(),
            'type' => Type\nullable(Type\non_empty_string()),
            'airportCode' => Type\nullable(Type\non_empty_string()),
            'optionsDescription' => Type\nullable(Type\non_empty_string()),
            'lastManual' => Type\nullable(ManualPublic::getTypeStructure()),
        ]);
    }

    /**
     * The collection exposes the lightweight projection of an equipment record, without
     * `lastManual` which is only computed on the single-item endpoint.
     */
    public static function getCollectionTypeStructure(): Type\TypeInterface
    {
        return self::getCollectionTypeStructureOf(self::getMemberTypeStructure());
    }

    public static function getPageTypeStructure(): Type\TypeInterface
    {
        return self::getPageTypeStructureOf(self::getMemberTypeStructure());
    }

    private static function getMemberTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\non_empty_string(),
            'id' => Type\int(),
            'serialNumber' => Type\non_empty_string(),
            'model' => Type\non_empty_string(),
            'type' => Type\nullable(Type\non_empty_string()),
            'airportCode' => Type\nullable(Type\non_empty_string()),
            'optionsDescription' => Type\nullable(Type\non_empty_string()),
        ], allowUnknownFields: true);
    }
}
