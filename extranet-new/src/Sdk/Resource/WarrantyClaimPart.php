<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * Lightweight part embedded in a {@see WarrantyClaim} item.
 *
 * Carries the legacy warranty part fields plus, when the part was shipped through a
 * Spare Parts Request (SPR), the few SPR fields flattened from the embedded relation.
 * It is not a fetchable API resource (no IRI) and is rendered through its own report table.
 */
final class WarrantyClaimPart
{
    public function __construct(
        public readonly string $partNumber,
        public readonly string $partDescription,
        public readonly string $quantity,
        public readonly string $unitOfMeasure,
        public readonly ?int $sprNumber = null,
        public readonly ?string $sprStatus = null,
        public readonly ?string $sprCreatedAt = null,
    ) {
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            'partNumber' => Type\string(),
            'partDescription' => Type\string(),
            'quantity' => Type\string(),
            'unitOfMeasure' => Type\string(),
            'spr' => Type\nullable(Type\shape([
                'id' => Type\int(),
                'status' => Type\string(),
                'createdAt' => Type\string(),
            ], allowUnknownFields: true)),
        ], allowUnknownFields: true);
    }
}
