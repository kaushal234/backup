<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(provider: CachedIONCollectionDataProvider::class),
        new Get(
            requirements: ['id' => '.*'],
            controller: NotFoundAction::class,
            output: false,
            read: false,
            provider: CachedIONItemDataProvider::class,
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['signal_code']],
    denormalizationContext: [],
)]
class SignalCode
{
    #[ApiProperty(identifier: true)]
    #[Groups(['signal_code'])]
    public string $code;

    #[Groups(['signal_code'])]
    public string $description;
}
