<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

class ServiceBulletin implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param list<ServiceBulletinEquipment> $equipments
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $createdAt,
        public readonly string $confidential,
        public readonly string $title,
        public readonly bool $partsNeeded = false,
        public readonly ?string $description = null,
        public readonly ?string $type = null,
        public readonly ?string $category = null,
        public readonly ?string $status = null,
        public readonly ?string $ssdDecidedAt = null,
        public readonly array $equipments = [],
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
            'id' => Type\int(),
            'title' => Type\non_empty_string(),
            'description' => Type\nullable(Type\non_empty_string()),
            'type' => Type\nullable(Type\string()),
            'confidential' => Type\non_empty_string(),
            'createdAt' => Type\non_empty_string(),
            'category' => Type\nullable(Type\string()),
            'status' => Type\nullable(Type\string()),
            'ssdDecidedAt' => Type\nullable(Type\string()),
            'partsNeeded' => Type\bool(),
            'lines' => Type\optional(Type\vec(Type\shape([
                'status' => Type\string(),
                'equipmentRecord' => ServiceBulletinEquipment::getTypeStructure(),
            ], allowUnknownFields: true))),
        ], allowUnknownFields: true);
    }
}
