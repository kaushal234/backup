<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

final class Amount
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly float|int $value,
        public readonly string $currency,
        public readonly string $unitOfMeasure,
    ) {
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('Amount'),
            'value' => Type\union(Type\float(), Type\int()),
            'currency' => Type\string(),
            'unitOfMeasure' => Type\non_empty_string(),
        ], allow_unknown_fields: true);
    }
}
