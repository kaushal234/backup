<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\CodeDefinitions\LogisticCodes;

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
            controller: NotFoundAction::class,
            output: false,
            read: false,
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['language']],
    denormalizationContext: [],
)]
class Language
{
    #[ApiProperty(identifier: true)]
    #[Groups(['language'])]
    public string $iso639_2;

    #[Groups(['language'])]
    public string $codeAlpha2;

    #[Groups(['language'])]
    public string $iso639;

    #[Groups(['language'])]
    public string $name;
}
