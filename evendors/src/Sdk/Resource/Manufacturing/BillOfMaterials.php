<?php

declare(strict_types=1);

namespace App\Sdk\Resource\Manufacturing;

use App\Sdk\Resource\ResourceInterface;
use Psl\Type;

/**
 * @template T
 *
 * @phpstan-type BillOfMaterialsStructure array{"@id": non-empty-string, "@type": non-empty-string, site: integer, product: non-empty-string, itemDescription: string, unitOfMeasure: non-empty-string, engineeringRevision: string, engineeringRevisionEffectiveDate: string, engineeringRevisionExpiryDate: string, expired: bool, level: integer}
 *
 * @psalm-type BillOfMaterialsStructure = array{"@id": non-empty-string, "@type": non-empty-string, site: integer, product: non-empty-string, itemDescription: string, unitOfMeasure: non-empty-string, engineeringRevision: string, engineeringRevisionEffectiveDate: string, engineeringRevisionExpiryDate: string, expired: bool, level: integer}
 *
 * @phpstan-type BillOfMaterialsItemStructure array{"@id": non-empty-string, "@type": non-empty-string, site: integer, partNumber: non-empty-string, itemDescription: string, quantity: int|float, unitOfMeasure: non-empty-string, engineeringRevision: string, engineeringRevisionEffectiveDate: string, engineeringRevisionExpiryDate: string, expired: bool, level: integer, children: T}
 *
 * @psalm-type BillOfMaterialsItemStructure = array{"@id": non-empty-string, "@type": non-empty-string, site: integer, partNumber: non-empty-string, itemDescription: string, quantity: int|float, unitOfMeasure: non-empty-string, engineeringRevision: string, engineeringRevisionEffectiveDate: string, engineeringRevisionExpiryDate: string, expired: bool, level: integer, children: T}
 */
final class BillOfMaterials implements ResourceInterface
{
    /**
     * @param list<BillOfMaterials> $children
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $site,
        public readonly string $partNumber,
        public readonly string $description,
        public readonly float $quantity,
        public readonly string $unitOfMeasure,
        public readonly ?string $revision,
        public readonly ?string $effectiveDate,
        public readonly ?string $expiryDate,
        public readonly bool $expired,
        public int $level = 0,
        public array $children = [],
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
            '@type' => Type\literal_scalar('BillOfMaterialItem'),
            'site' => Type\int(),
            'product' => Type\non_empty_string(),
            'itemDescription' => Type\string(),
            'quantity' => Type\optional(Type\union(Type\int(), Type\float())),
            'unitOfMeasure' => Type\non_empty_string(),
            'engineeringRevision' => Type\nullable(Type\string()),
            'engineeringRevisionEffectiveDate' => Type\nullable(Type\string()),
            'engineeringRevisionExpiryDate' => Type\nullable(Type\string()),
            'level' => Type\optional(Type\int()),
            'expired' => Type\bool(),
        ], allow_unknown_fields: true);
    }
}
