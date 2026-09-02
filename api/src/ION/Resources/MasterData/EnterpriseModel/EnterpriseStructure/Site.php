<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\EnterpriseModel\EnterpriseStructure;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(provider: CachedIONCollectionDataProvider::class),
        new Get(
            requirements: ['id' => '.*'],
            controller: NotFoundAction::class,
            output: false,
            read: false,
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['site']],
    denormalizationContext: [],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
)]
class Site
{
    #[ApiProperty(identifier: true)]
    #[Groups(['site', 'site:light'])]
    public string $siteID;

    #[Groups(['site', 'site:light'])]
    public string $siteDescription;

    #[Groups(['site'])]
    public string $siteAddressCode;

    #[Groups(['site'])]
    public string $siteAddressName;
}
