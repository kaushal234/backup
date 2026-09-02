<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

class EquipmentRecord implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param list<Manual> $manuals
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $serialNumber,
        public readonly string $product,
        public readonly int $legacyId,
        public readonly ?string $optionsDescription = null,
        public readonly ?Customer $customer = null,
        public readonly ?string $productType = null,
        public readonly ?Airport $airport = null,
        public readonly ?string $customerSerialNumber = null,
        public readonly ?string $location = null,
        public readonly ?int $manufacturerLocationErp = null,
        public readonly ?string $projectNumber = null,
        public readonly ?Location $salesOrganisationService = null,
        public readonly array $manuals = [],
        public readonly ?\DateTime $dateShipped = null,
        public readonly ?\DateTime $dateCommissioned = null,
        public readonly ?EmissionRating $emissionRating = null,
        public readonly ?int $hourMeter = null,
        public readonly ?\DateTime $warrantyEndDate = null,
        public readonly ?string $warrantyConditions = null,
        public readonly ?string $warrantyStatus = null,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getSimplifiedStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'id' => Type\int(),
            'model' => Type\non_empty_string(),
            'serialNumber' => Type\non_empty_string(),
            'legacyId' => Type\int(),
        ], allowUnknownFields: true);
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'id' => Type\int(),
            'legacyId' => Type\int(),
            'serialNumber' => Type\non_empty_string(),
            'endUser' => Type\nullable(Customer::getTypeStructure()),
            'model' => Type\non_empty_string(),
            'type' => Type\nullable(Type\non_empty_string()),
            'airport' => Type\optional(Type\nullable(Airport::getTypeStructure())),
            'customerSerialNumber' => Type\nullable(Type\non_empty_string()),
            'location' => Type\nullable(Type\non_empty_string()),
            'optionsDescription' => Type\optional(Type\nullable(Type\non_empty_string())),
            'manuals' => Type\optional(Type\vec(Manual::getStructureForEquipment())),
            'salesOrganisationService' => Type\nullable(Location::getTypeStructure()),
            'dateShipped' => Type\nullable(Type\non_empty_string()),
            'dateCommissioned' => Type\nullable(Type\non_empty_string()),
            'emissionRating' => Type\optional(Type\nullable(EmissionRating::getTypeStructure())),
            'hourMeter' => Type\optional(Type\nullable(Type\int())),
            'warrantyEndDate' => Type\optional(Type\nullable(Type\non_empty_string())),
            'warrantyConditions' => Type\optional(Type\nullable(Type\string())),
            'warrantyStatus' => Type\optional(Type\nullable(Type\non_empty_string())),
        ], allowUnknownFields: true);
    }

    public static function getPageTypeStructure(): Type\TypeInterface
    {
        return self::getPageTypeStructureOf(Type\shape([
            '@id' => Type\non_empty_string(),
            'id' => Type\int(),
            'legacyId' => Type\int(),
            'serialNumber' => Type\non_empty_string(),
            'endUser' => Type\nullable(Customer::getTypeStructure()),
            'model' => Type\non_empty_string(),
            'type' => Type\nullable(Type\non_empty_string()),
            'airport' => Type\nullable(Airport::getTypeStructure()),
            'customerSerialNumber' => Type\nullable(Type\non_empty_string()),
            'location' => Type\nullable(Type\non_empty_string()),
            'manufacturerLocation' => Type\optional(Type\nullable(Location::getTypeStructure())),
            'optionsDescription' => Type\optional(Type\nullable(Type\non_empty_string())),
            'projectNumber' => Type\optional(Type\nullable(Type\non_empty_string())),
            'manuals' => Type\optional(Type\vec(Manual::getStructureForEquipment())),
            'salesOrganisationService' => Type\nullable(Location::getTypeStructure()),
            'dateShipped' => Type\nullable(Type\non_empty_string()),
            'dateCommissioned' => Type\nullable(Type\non_empty_string()),
        ], allowUnknownFields: true));
    }

    public static function getEmbedStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'id' => Type\int(),
            'legacyId' => Type\int(),
            'serialNumber' => Type\non_empty_string(),
            'endUser' => Type\nullable(Customer::getTypeStructure()),
            'model' => Type\non_empty_string(),
            'type' => Type\nullable(Type\non_empty_string()),
            'airport' => Type\nullable(Airport::getTypeStructure()),
            'customerSerialNumber' => Type\nullable(Type\non_empty_string()),
            'location' => Type\nullable(Type\non_empty_string()),
            'optionsDescription' => Type\optional(Type\nullable(Type\non_empty_string())),
            'manuals' => Type\optional(Type\vec(Manual::getStructureForEquipment())),
            'salesOrganisationService' => Type\nullable(Location::getTypeStructure()),
        ], allowUnknownFields: true);
    }
}
