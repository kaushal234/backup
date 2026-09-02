<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

class WarrantyClaim implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param list<WarrantyClaimPart> $parts
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $status,
        public readonly ?string $claimDate,
        public readonly string $type,
        public readonly string $equipmentModel,
        public readonly ?string $serialNumber,
        public readonly ?string $customerName,
        public readonly ?string $equipmentLocation,
        public readonly ?int $equipmentHours = null,
        public readonly ?string $claimantDetails = null,
        public readonly ?string $details = null,
        public readonly ?string $enteredByUsername = null,
        public readonly ?string $description = null,
        public readonly array $parts = [],
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@context' => Type\optional(Type\non_empty_string()),
            'equipmentHours' => Type\optional(Type\nullable(Type\int())),
            'claimantDetails' => Type\optional(Type\nullable(Type\string())),
            'details' => Type\optional(Type\nullable(Type\string())),
            'enteredBy' => Type\optional(Type\nullable(Type\shape([
                '@id' => Type\non_empty_string(),
                '@type' => Type\non_empty_string(),
                'username' => Type\string(),
            ],
                allowUnknownFields: false))
            ),
            '@id' => Type\non_empty_string(),
            '@type' => Type\non_empty_string(),
            'status' => Type\non_empty_string(),
            'claimDate' => Type\nullable(Type\string()),
            'type' => Type\string(),
            'equipmentModel' => Type\string(),
            'serialNumber' => Type\nullable(Type\string()),
            'customerName' => Type\nullable(Type\string()),
            'equipmentLocation' => Type\nullable(Type\string()),
            'description' => Type\nullable(Type\string()),
            'parts' => Type\optional(Type\vec(WarrantyClaimPart::getTypeStructure())),
        ], allowUnknownFields: false);
    }
}
