<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\CodeDefinitions\LogisticCodes;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\IONFilter;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    shortName: 'ionCountries',
    operations: [
        new GetCollection(
            uriTemplate: '/countries',
            normalizationContext: ['groups' => ['country']],
            provider: CachedIONCollectionDataProvider::class,
        ),
        new Get(uriTemplate: '/countries/{country}', requirements: ['country' => '.*'], provider: CachedIONItemDataProvider::class),
    ],
    routePrefix: 'ion',
    normalizationContext: ['country:detail'],
    denormalizationContext: [],
)]
#[ApiFilter(IONFilter::class, properties: ['description', 'country'])]
class Country
{
    #[ApiProperty(identifier: true)]
    #[Groups(['country', 'country:detail'])]
    public string $country;

    #[Groups(['country', 'country:detail'])]
    public string $description;

    #[Groups(['country:detail'])]
    public string $isoCodeAlpha2 = '';

    #[Groups(['country:detail'])]
    public string $isoCodeAlpha3 = '';

    #[Groups(['country:detail'])]
    public string $telephone = '';

    #[Groups(['country:detail'])]
    public string $telex = '';

    #[Groups(['country:detail'])]
    public string $fax = '';

    #[Groups(['country:detail'])]
    public string $euMember = 'no';

    #[Groups(['country:detail'])]
    public string $currency = '';
}
