<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

class File implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $filePath,
        public readonly ?string $description = null,
        public readonly ?People $poster = null,
        public readonly ?\DateTime $createdAt = null,
        public readonly ?string $extension = null,
        public readonly ?int $size = null,
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
            'filePath' => Type\non_empty_string(),
            'description' => Type\nullable(Type\non_empty_string()),
            'poster' => Type\optional(Type\nullable(People::getTypeStructure())),
            'createdAt' => Type\non_empty_string(),
            'extension' => Type\optional(Type\nullable(Type\non_empty_string())),
            'size' => Type\optional(Type\nullable(Type\int())),
        ], allowUnknownFields: true);
    }
}
