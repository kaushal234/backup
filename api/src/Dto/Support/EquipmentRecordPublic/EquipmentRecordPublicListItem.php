<?php

declare(strict_types=1);

namespace App\Dto\Support\EquipmentRecordPublic;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\Entity\Common\Airport;
use App\Entity\EquipmentRecord;
use App\Filter\SimpleSearchFilter;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/public/equipment_records',
            paginationItemsPerPage: 25,
            paginationClientItemsPerPage: true,
            order: ['serialNumber' => 'DESC'],
            security: "is_granted('AUTHORIZED_APPLICATION_FEATURE_EXTRANET_PUBLIC')",
            name: 'equipment_record_public_collection',
            stateOptions: new Options(entityClass: EquipmentRecord::class),
        ),
        new Get(
            controller: NotFoundAction::class,
            output: false,
            read: false,
        ),
    ],
)]
#[Map(source: EquipmentRecord::class)]
#[ApiFilter(SimpleSearchFilter::class, properties: ['serialNumber', 'model'])]
#[ApiFilter(OrderFilter::class, properties: ['serialNumber', 'model', 'type', 'airport.code'])]
class EquipmentRecordPublicListItem
{
    public int $id;

    public string $serialNumber;

    public ?string $model = null;

    public ?string $type = null;

    #[Map(source: 'airport', transform: [self::class, 'airportToCode'])]
    public ?string $airportCode = null;

    public ?string $optionsDescription = null;

    public static function airportToCode(?Airport $airport): ?string
    {
        return $airport?->getCode();
    }
}
