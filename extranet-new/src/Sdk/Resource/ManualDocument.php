<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

class ManualDocument implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param array<Part> $parts
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public int $position,
        public string $description,
        public string $type,
        public string $factoryNumber,
        public ?string $revision = null,
        public ?Category $category = null,
        public ?string $otherDescription = null,
        public readonly ?Manual $manual = null,
        public readonly ?File $document = null,
        public array $parts = [],
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getSimplifiedTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'position' => Type\int(),
            'revision' => Type\optional(Type\non_empty_string()),
            'category' => Type\nullable(Category::getTypeStructure()),
            'description' => Type\string(),
            'otherDescription' => Type\nullable(Type\string()),
            'type' => Type\non_empty_string(),
            'factoryNumber' => Type\non_empty_string(),
            'document' => Type\nullable(File::getTypeStructure()),
        ], allowUnknownFields: true);
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'manual' => Manual::getSimplifiedStructure(),
            'position' => Type\int(),
            'revision' => Type\optional(Type\non_empty_string()),
            'category' => Type\nullable(Category::getTypeStructure()),
            'description' => Type\string(),
            'otherDescription' => Type\nullable(Type\string()),
            'type' => Type\non_empty_string(),
            'factoryNumber' => Type\non_empty_string(),
            'document' => Type\nullable(File::getTypeStructure()),
            'parts' => Type\optional(Type\vec(Part::getTypeStructure())),
        ], allowUnknownFields: true);
    }
}
