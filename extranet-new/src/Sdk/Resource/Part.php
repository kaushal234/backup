<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

final class Part implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $partNumber,
        public readonly int $quantity,
        public readonly int $position,
        public readonly string $unitOfMeasure,
        public readonly string $description,
        public readonly bool $preventive,
        public readonly bool $maintenance,
        public readonly bool $overhaul,
        public readonly bool $critical,
        public readonly ?string $otherDescription = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->iri;
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'partNumber' => Type\non_empty_string(),
            'quantity' => Type\int(),
            'position' => Type\int(),
            'unitOfMeasure' => Type\non_empty_string(),
            'description' => Type\non_empty_string(),
            'otherDescription' => Type\nullable(Type\non_empty_string()),
            'preventive' => Type\bool(),
            'maintenance' => Type\bool(),
            'overhaul' => Type\bool(),
            'critical' => Type\bool(),
        ], allowUnknownFields: true);
    }
}
