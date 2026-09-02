<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

class EquipmentSerial implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly int $legacyId,
        public readonly ?EquipmentSerialComponent $component = null,
        public readonly ?string $model = null,
        public readonly ?string $serial = null,
        public readonly ?string $brand = null,
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
            'legacyId' => Type\int(),
            'component' => Type\nullable(EquipmentSerialComponent::getTypeStructure()),
            'model' => Type\nullable(Type\non_empty_string()),
            'serial' => Type\nullable(Type\non_empty_string()),
            'brand' => Type\nullable(Type\non_empty_string()),
        ], allowUnknownFields: true);
    }
}
