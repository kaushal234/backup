<?php

declare(strict_types=1);

namespace App\ION\Resources\Engineering;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
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
    normalizationContext: ['groups' => ['engineering:item']],
    denormalizationContext: [],
)]
#[ApiFilter(DataAreaFilter::class, properties: ['itemsList', 'project', 'languageID'])]
class EngineeringItemDescription
{
    #[ApiProperty(identifier: true)]
    #[Groups(['engineering:item'])]
    public string $item;

    #[Groups(['engineering:item'])]
    public string $project = '';

    #[Groups(['engineering:item'])]
    public string $description = '';
}
