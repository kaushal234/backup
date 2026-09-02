<?php

declare(strict_types=1);

namespace App\Sdk\Resource\SupplierRanking;

use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\Supplier;
use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-import-type SupplierStructure from Supplier
 *
 * @psalm-import-type SupplierStructure from Supplier
 *
 * @phpstan-import-type NotationStructure from Notation
 *
 * @psalm-import-type NotationStructure from Notation
 *
 * @phpstan-type SupplierRankingStructure array{"@id": non-empty-string, "id": positive-int, supplier: SupplierStructure, notations: list<NotationStructure>}
 *
 * @psalm-type SupplierRankingStructure = array{"@id": non-empty-string, "id": positive-int, supplier: SupplierStructure, notations: list<NotationStructure>}
 */
final class SupplierRanking implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param list<Notation> $notations
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly Supplier $supplier,
        public readonly array $notations,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<SupplierRankingStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'id' => Type\int(),
            'supplier' => Supplier::getTypeStructure(),
            'notations' => Type\optional(Type\vec(Notation::getTypeStructure())),
        ], allow_unknown_fields: true);
    }
}
