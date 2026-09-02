<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * @phpstan-type VendorUserStructure array{"@id": non-empty-string, "@type": non-empty-string, name: non-empty-string}
 *
 * @psalm-type VendorUserStructure = array{"@id": non-empty-string, "@type": non-empty-string, name: non-empty-string}
 */
class Responsible implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly string $name,
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
            '@type' => Type\literal_scalar('Responsible'),
            'name' => Type\non_empty_string(),
        ], allow_unknown_fields: true);
    }
}
