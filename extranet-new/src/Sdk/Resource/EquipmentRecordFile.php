<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

class EquipmentRecordFile implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $displayFilename,
        public readonly ?string $description = null,
        public readonly ?\DateTime $date = null,
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
            'displayFilename' => Type\non_empty_string(),
            'description' => Type\nullable(Type\string()),
            'date' => Type\nullable(Type\non_empty_string()),
        ], allowUnknownFields: true);
    }
}
