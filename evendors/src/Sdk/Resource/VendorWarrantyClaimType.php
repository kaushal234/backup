<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * @phpstan-type VendorWarrantyClaimTypeStructure array{"@id": non-empty-string, "@type": non-empty-string, name: string, description: string}
 *
 * @psalm-type VendorWarrantyClaimTypeStructure = array{"@id": non-empty-string, "@type": non-empty-string, name: string, description: string}
 */
final class VendorWarrantyClaimType implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly string $name,
        public readonly string $description,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<VendorWarrantyClaimTypeStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('VendorWarrantyClaimType'),
            'name' => Type\string(),
            'description' => Type\string(),
        ], allow_unknown_fields: true);
    }
}
