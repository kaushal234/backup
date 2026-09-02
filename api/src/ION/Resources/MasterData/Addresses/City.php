<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\Addresses;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\Filter\IONFilter;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(provider: CachedIONCollectionDataProvider::class),
        new Get(requirements: ['id' => '.*'], controller: NotFoundAction::class, output: false, read: false),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['city']],
    denormalizationContext: [],
)]
#[ApiFilter(IONFilter::class, properties: ['description', 'country'])]
class City
{
    #[ApiProperty(identifier: true)]
    #[Groups(['city'])]
    public string $city;

    #[Groups(['city'])]
    public string $state;

    #[Groups(['city'])]
    public string $country;

    #[Groups(['city'])]
    public string $description;
}
