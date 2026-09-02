<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

final class NonConformityPart implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly ?string $reference,
        public readonly ?string $referenceNumber,
        public readonly ?string $serialNumber,
        public readonly string $createdAt,
        public readonly ?Person $createdBy,
        public readonly string $partNumber,
        public readonly string $description,
        public readonly int $quantity,
        public readonly string $unitOfMeasure,
        public readonly ?string $deletedAt = null,
        public readonly ?Person $deletedBy = null,
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
            '@type' => Type\literal_scalar('NonConformityPart'),
            'reference' => Type\nullable(Type\string()),
            'referenceNumber' => Type\nullable(Type\string()),
            'serialNumber' => Type\nullable(Type\string()),
            'createdAt' => Type\string(),
            'createdBy' => Type\nullable(Person::getTypeStructure()),
            'partNumber' => Type\string(),
            'description' => Type\string(),
            'quantity' => Type\int(),
            'unitOfMeasure' => Type\string(),
            'deletedAt' => Type\nullable(Type\string()),
            'deletedBy' => Type\nullable(Person::getTypeStructure()),
            'id' => Type\int(),
        ], allow_unknown_fields: true);
    }
}
