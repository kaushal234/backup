<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\ShopLayout\Miscellaneous;

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
        new Get(requirements: ['id' => '.*'], controller: NotFoundAction::class, output: false, read: false, provider: CachedIONItemDataProvider::class),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['task']],
    denormalizationContext: [],
    security: "is_granted('ACCESS_PEOPLE')",
)]
#[ApiFilter(DataAreaFilter::class, properties: ['checkActive'])]
class Task
{
    #[ApiProperty(identifier: true)]
    #[Groups(['task'])]
    public string $code;

    #[Groups(['task'])]
    public string $description;

    #[Groups(['task'])]
    public string $type;
}
