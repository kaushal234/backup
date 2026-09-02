<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\BusinessPartners;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\Filter\DataAreaFilter;

#[ApiResource(
    operations: [new GetCollection(provider: CachedIONCollectionDataProvider::class)],
    routePrefix: 'ion',
)]
#[ApiFilter(DataAreaFilter::class, properties: ['title', 'orderLineDateAfter', 'orderLineDateBefore'])]
class Turnover
{
    #[ApiProperty(identifier: true)]
    public string $code;

    public string $name;

    public float $turnover;

    public string $countryCode;

    public string $currencyCode;

    public string $currencyName;
}
