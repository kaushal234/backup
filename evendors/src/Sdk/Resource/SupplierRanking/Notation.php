<?php

declare(strict_types=1);

namespace App\Sdk\Resource\SupplierRanking;

use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-import-type CriteriaStructure from Criteria
 *
 * @psalm-import-type CriteriaStructure from Criteria
 *
 * @phpstan-type NotationStructure array{"@id": non-empty-string, criteria: CriteriaStructure, notation?: positive-int}
 *
 * @psalm-type NotationStructure = array{"@id": non-empty-string, criteria: CriteriaStructure, notation?: positive-int}
 */
final class Notation implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly Criteria $criteria,
        public readonly ?int $notation,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<NotationStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'criteria' => Criteria::getTypeStructure(),
            'notation' => Type\nullable(Type\int()),
        ], allow_unknown_fields: true);
    }
}
