<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\CodeDefinitions\LogisticCodes;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['carrier']], provider: CachedIONCollectionDataProvider::class),
        new Get(requirements: ['id' => '.*'], provider: CachedIONItemDataProvider::class),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['carrier', 'carrier:detail']],
    denormalizationContext: [],
)]
class Carrier
{
    #[ApiProperty(identifier: true)]
    #[Groups(['carrier'])]
    public string $code;

    #[Groups(['carrier'])]
    public string $name;

    #[Groups(['carrier'])]
    public ?string $url = null;

    #[Groups(['carrier:detail'])]
    public ?BusinessPartner $buyFromBusinessPartner = null;
}
