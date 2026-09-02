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
 * @phpstan-type SupplierCorrectiveActionRequestMainFileStructure array{"@id": non-empty-string, "@type": "SupplierCorrectiveActionRequestFile", "id": positive-int, "filePath": non-empty-string, "poster": ?PersonStructure, createdAt: non-empty-string, "description": null|string, "sha": string, "mimeType": string, "extension": string, "size": int}
 *
 * @psalm-type SupplierCorrectiveActionRequestMainFileStructure = array{"@id": non-empty-string, "@type": "SupplierCorrectiveActionRequestFile", "id": positive-int, "filePath": non-empty-string, "poster": ?PersonStructure, createdAt: non-empty-string, "description": null|string, "sha": string, "mimeType": string, "extension": string, "size": int}
 */
final class SupplierCorrectiveActionRequestMainFile implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $filePath,
        public readonly ?Person $poster,
        public readonly string $createdAt,
        public readonly ?string $description,
        public readonly string $sha,
        public readonly string $mimeType,
        public readonly string $extension,
        public readonly int $size,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<SupplierCorrectiveActionRequestMainFileStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('SupplierCorrectiveActionRequestMainFile'),
            'id' => Type\positive_int(),
            'filePath' => Type\non_empty_string(),
            'poster' => Type\nullable(Person::getTypeStructure()),
            'createdAt' => Type\non_empty_string(),
            'description' => Type\nullable(Type\string()),
            'sha' => Type\string(),
            'mimeType' => Type\string(),
            'extension' => Type\string(),
            'size' => Type\int(),
        ], allow_unknown_fields: true);
    }
}
