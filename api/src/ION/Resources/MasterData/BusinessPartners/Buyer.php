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
#[ApiFilter(DataAreaFilter::class, properties: ['code', 'site'])]
class Buyer
{
    #[ApiProperty(identifier: true)]
    public string $supplierCode;

    #[ApiProperty(identifier: true)]
    public string $erpCode;

    public string $department;

    public string $buyerCode;
}
