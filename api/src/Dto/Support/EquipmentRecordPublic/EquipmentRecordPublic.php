<?php

declare(strict_types=1);

namespace App\Dto\Support\EquipmentRecordPublic;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\DataProvider\EquipmentRecordPublicProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/public/equipment_records/{serialNumber}',
            security: "is_granted('AUTHORIZED_APPLICATION_FEATURE_EXTRANET_PUBLIC')",
            name: 'equipment_record_public',
            provider: EquipmentRecordPublicProvider::class,
        ),
    ],
)]
class EquipmentRecordPublic
{
    #[ApiProperty(identifier: false)]
    public int $id;

    #[ApiProperty(identifier: true)]
    public string $serialNumber;

    public ?string $model = null;

    public ?string $type = null;

    public ?string $airportCode = null;

    public ?string $optionsDescription = null;

    public ?ManualPublic $lastManual = null;
}
