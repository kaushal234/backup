<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-import-type PersonStructure from Person
 *
 * @psalm-import-type PersonStructure from Person
 *
 * @phpstan-type LegacyFileStructure array{"@id": non-empty-string, "@type": "LegacyFile", "id": positive-int, "filePath": non-empty-string, "poster": null|PersonStructure, createdAt: non-empty-string, "description": null|string}
 *
 * @psalm-type LegacyFileStructure = array{"@id": non-empty-string, "@type": "LegacyFile", "id": positive-int, "filePath": non-empty-string, "poster": null|PersonStructure, createdAt: non-empty-string, "description": null|string}
 */
final class LegacyFile implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $filePath,
        public readonly ?Person $poster,
        public readonly string $createdAt,
        public readonly ?string $description,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<LegacyFileStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('LegacyFile'),
            'id' => Type\positive_int(),
            'filePath' => Type\non_empty_string(),
            'poster' => Type\nullable(Person::getTypeStructure()),
            'createdAt' => Type\non_empty_string(),
            'description' => Type\optional(Type\nullable(Type\string())),
        ], allow_unknown_fields: true);
    }
}
