<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\ION\DataProvider\CachedIONItemDataProvider;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.*'], controller: NotFoundAction::class, output: false, read: false),
    ],
    routePrefix: 'ion',
    normalizationContext: [],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
class BillOfMaterialsSecurity
{
    #[ApiProperty(identifier: true)]
    public string $contactCode;

    #[ApiProperty(identifier: true)]
    public string $project = '';

    #[ApiProperty(identifier: true)]
    public string $item;

    #[ApiProperty(identifier: true)]
    public string $site;

    public bool $isGranted;
}
