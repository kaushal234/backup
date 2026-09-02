<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-type LocationStructure array{"@id": non-empty-string, "@type": non-empty-string, name: non-empty-string, erp: ?positive-int}
 *
 * @psalm-type LocationStructure = array{"@id": non-empty-string, "@type": non-empty-string, name: non-empty-string, erp: ?positive-int}
 */
final class Location implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param non-empty-string $iri
     * @param non-empty-string $name
     */
    public function __construct(
        public readonly string $iri,
        public readonly string $name,
        public readonly ?int $erp = null,
        public readonly ?Currency $currency = null,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('Location'),
            'name' => Type\non_empty_string(),
            'currency' => Type\optional(Type\nullable(Currency::getTypeStructure())),
        ], allow_unknown_fields: true);
    }
}
