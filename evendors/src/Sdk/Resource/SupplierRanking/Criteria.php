<?php

declare(strict_types=1);

namespace App\Sdk\Resource\SupplierRanking;

use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-type CriteriaStructure array{"@id": non-empty-string, "id": positive-int, name: non-empty-string, public: bool}
 *
 * @psalm-type CriteriaStructure = array{"@id": non-empty-string, "id": positive-int, name: non-empty-string, public: bool}
 */
final class Criteria implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $name,
        public readonly bool $public,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<CriteriaStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'id' => Type\int(),
            'name' => Type\non_empty_string(),
            'public' => Type\bool(),
        ], allow_unknown_fields: true);
    }
}
