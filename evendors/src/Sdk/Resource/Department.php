<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * @phpstan-type DepartmentStructure array{"@id": non-empty-string, "@type": non-empty-string, name: non-empty-string}
 *
 * @psalm-type DepartmentStructure = array{"@id": non-empty-string, "@type": non-empty-string, name: non-empty-string}
 *
 * @uses CompleteTypeStructureTrait<DepartmentStructure>
 */
final class Department implements ResourceInterface
{
    use Traits\CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly string $name,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<DepartmentStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('Department'),
            'name' => Type\non_empty_string(),
        ], allow_unknown_fields: true);
    }
}
