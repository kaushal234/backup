<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\BillOfMaterials;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Manufacturing\JobShop\EngineeringRevisionTrait;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.*']),
    ],
    routePrefix: 'ion/bill-of-materials',
    normalizationContext: ['groups' => ['bom', 'ion:engineering:revision', 'ion:item']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
#[ApiFilter(DataAreaFilter::class, properties: ['date', 'otherLanguage'])]
class ShopfloorView extends BillOfMaterials
{
    use EngineeringRevisionTrait;

    #[Groups(['bom'])]
    public string $engineeringSignalCode;

    #[Groups(['bom'])]
    public string $extraInformation;

    #[Groups(['bom'])]
    public string $warehouse;

    #[Groups(['bom'])]
    public bool $backflushIfMaterial;

    #[Groups(['bom'])]
    public bool $phantom;

    /**
     * @var Collection<ShopfloorViewItem>
     */
    protected Collection $items;
}
