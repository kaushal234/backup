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
 * @phpstan-type SupplierCorrectiveActionRequestFileStructure array{"@id": non-empty-string, "@type": "SupplierCorrectiveActionRequestFile", "id": positive-int, "filePath": non-empty-string, "poster": ?PersonStructure, createdAt: non-empty-string, "description": null|string, "sha": string, "mimeType": string, "extension": string, "size": int}
 *
 * @psalm-type SupplierCorrectiveActionRequestFileStructure = array{"@id": non-empty-string, "@type": "SupplierCorrectiveActionRequestFile", "id": positive-int, "filePath": non-empty-string, "poster": ?PersonStructure, createdAt: non-empty-string, "description": null|string, "sha": string, "mimeType": string, "extension": string, "size": int}
 */
final class SupplierCorrectiveActionRequestFile implements ResourceInterface
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
        public readonly bool $public = true,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<SupplierCorrectiveActionRequestFileStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('SupplierCorrectiveActionRequestFile'),
            'id' => Type\positive_int(),
            'filePath' => Type\non_empty_string(),
            'poster' => Type\nullable(Person::getTypeStructure()),
            'createdAt' => Type\non_empty_string(),
            'description' => Type\optional(Type\nullable(Type\string())),
            'sha' => Type\string(),
            'mimeType' => Type\string(),
            'extension' => Type\string(),
            'size' => Type\int(),
            'public' => Type\optional(Type\bool()),
        ], allow_unknown_fields: true);
    }
}
