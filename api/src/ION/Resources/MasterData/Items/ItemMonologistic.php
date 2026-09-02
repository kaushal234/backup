<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\Items;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\IONFilter;
use App\ION\Filter\SelectionFilter;
use App\ION\Resources\ItemProviderIdentifierInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['item:list']], provider: CachedIONCollectionDataProvider::class),
        new Get(requirements: ['id' => '.*'], provider: CachedIONItemDataProvider::class),
    ],
    routePrefix: 'ion',
    security: "is_granted('ACCESS_PEOPLE')",
)]
#[ApiFilter(SelectionFilter::class)]
#[ApiFilter(IONFilter::class, properties: ['itemCode'])]
class ItemMonologistic implements ItemProviderIdentifierInterface
{
    public const ION_PROJECT_IDENTIFIER = '         ';

    #[ApiProperty(identifier: true)]
    #[Groups(['item', 'item:list'])]
    public string $itemCode;

    #[Groups(['item', 'item:list'])]
    public string $description;

    #[Groups(['item'])]
    public ?string $unitOfMeasure;

    public static function getIdentifier(array $identifier): array
    {
        $identifier['itemCode'] = self::ION_PROJECT_IDENTIFIER.$identifier['itemCode'];

        return $identifier;
    }
}
