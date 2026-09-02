<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-import-type RegionStructure from Region
 *
 * @psalm-import-type RegionStructure from Region
 *
 * @phpstan-type BusinessUnitStructure array{"@id": non-empty-string, "@type": non-empty-string, name: non-empty-string, "region": RegionStructure}
 *
 * @psalm-type BusinessUnitStructure = array{"@id": non-empty-string, "@type": non-empty-string, name: non-empty-string, "region": RegionStructure}
 *
 * @uses CompleteTypeStructureTrait<BusinessUnitStructure>
 */
final class BusinessUnit implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly string $name,
        public readonly Region $region,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<BusinessUnitStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('BusinessUnit'),
            'name' => Type\non_empty_string(),
            'region' => Region::getTypeStructure(),
        ], allow_unknown_fields: true);
    }
}
