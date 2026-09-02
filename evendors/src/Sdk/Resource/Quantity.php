<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

final class Quantity
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $value,
        public readonly string $unitOfMeasure,
    ) {
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('Quantity'),
            'value' => Type\union(Type\float(), Type\int()),
            'unitOfMeasure' => Type\string(),
        ], allow_unknown_fields: true);
    }
}
