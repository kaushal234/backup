<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

class EquipmentSerialComponent
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $signalCode = null
    ) {
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            'id' => Type\int(),
            'name' => Type\non_empty_string(),
            'signalCode' => Type\nullable(Type\non_empty_string()),
        ], allowUnknownFields: true);
    }
}
