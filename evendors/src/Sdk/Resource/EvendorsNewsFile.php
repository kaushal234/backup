<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-type EvendorsNewsFileStructure array{"@id": non-empty-string, "@type": non-empty-string,"id": positive-int, "filePath": non-empty-string, "extension": non-empty-string}
 *
 * @psalm-type EvendorsNewsFileStructure = array{"@id": non-empty-string, "@type": non-empty-string,"id": positive-int, "filePath": non-empty-string, "extension": non-empty-string}
 */
class EvendorsNewsFile implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly string $resource,
        public readonly int $id,
        public readonly string $filePath,
        public readonly string $extension,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<EvendorsNewsFileStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('EvendorsNewsFile'),
            'id' => Type\int(),
            'filePath' => Type\non_empty_string(),
            'extension' => Type\non_empty_string(),
        ], allow_unknown_fields: true);
    }
}
