<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * @phpstan-type SupplierCorrectiveActionRequestPartStructure array{"@id": non-empty-string, "@type": non-empty-string, partNumber: string, description: string, quantity: float, unitOfMeasure: null|string}
 *
 * @psalm-type  SupplierCorrectiveActionRequestPartStructure = array{"@id": non-empty-string, "@type": non-empty-string, partNumber: string, description: string, quantity: float, unitOfMeasure: null|string}
 */
final class SupplierCorrectiveActionRequestPart implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly string $partNumber,
        public readonly string $description,
        public readonly float $quantity,
        public readonly ?string $unitOfMeasure = null,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<SupplierCorrectiveActionRequestPartStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\non_empty_string(),
            'partNumber' => Type\non_empty_string(),
            'description' => Type\string(),
            'quantity' => Type\union(Type\int(), Type\float()),
            'unitOfMeasure' => Type\nullable(Type\string()),
        ], allow_unknown_fields: true);
    }
}
