<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

class Manual implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param array<ManualDocument> $documents
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly array $documents = [],
        public readonly ?\DateTime $createdAt = null,
        public readonly ?string $features = null,
        public readonly ?string $description = null,
        public readonly ?string $language = null,
        public readonly ?string $status = null,
        public readonly ?EquipmentRecord $equipment = null,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getSimplifiedStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'equipmentRecord' => EquipmentRecord::getSimplifiedStructure(),
        ], allowUnknownFields: true);
    }

    public static function getStructureForEquipment(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'features' => Type\nullable(Type\non_empty_string()),
            'description' => Type\nullable(Type\non_empty_string()),
            'language' => Type\optional(Type\nullable(Type\non_empty_string())),
            'createdAt' => Type\optional(Type\nullable(Type\non_empty_string())),
        ], allowUnknownFields: true);
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'createdAt' => Type\nullable(Type\non_empty_string()),
            'status' => Type\non_empty_string(),
            'documents' => Type\optional(Type\vec(ManualDocument::getSimplifiedTypeStructure())),
            'features' => Type\nullable(Type\non_empty_string()),
            'description' => Type\nullable(Type\non_empty_string()),
            'language' => Type\nullable(Type\non_empty_string()),
            'equipmentRecord' => EquipmentRecord::getSimplifiedStructure(),
        ], allowUnknownFields: true);
    }
}
