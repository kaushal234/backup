<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * Lightweight equipment embedded in a {@see ServiceBulletin} item.
 *
 * Built from the legacy equipment record linked through the service bulletin lines.
 * It only carries the few fields available on that legacy entity, so it is not a
 * fetchable API resource (no IRI) and is rendered through its own light data table.
 */
final class ServiceBulletinEquipment
{
    public function __construct(
        public readonly int $id,
        public readonly string $serialNumber,
        public readonly string $model,
        public readonly string $type,
        public readonly string $customerName,
    ) {
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            'id' => Type\int(),
            'serialNumber' => Type\string(),
            'model' => Type\string(),
            'type' => Type\string(),
            'customerName' => Type\string(),
        ], allowUnknownFields: true);
    }
}
