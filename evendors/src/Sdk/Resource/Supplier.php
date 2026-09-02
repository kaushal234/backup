<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-type SupplierStructure array{"@id": non-empty-string, "name": non-empty-string, code: non-empty-string}
 *
 * @psalm-type SupplierStructure = array{"@id": non-empty-string, "name": non-empty-string, code: non-empty-string}
 */
final class Supplier implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param non-empty-string $iri
     * @param non-empty-string $name
     * @param non-empty-string $code
     */
    public function __construct(
        public readonly string $iri,
        public readonly string $name,
        public readonly string $code,
    ) {
    }

    /**
     * @return Type\TypeInterface<SupplierStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'name' => Type\non_empty_string(),
            'code' => Type\non_empty_string(),
        ], allow_unknown_fields: true);
    }

    public function getIri(): string
    {
        return $this->iri;
    }
}
