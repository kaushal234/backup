<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\BillOfMaterials;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Resources\Manufacturing\JobShop\EngineeringRevisionTrait;
use App\ION\Resources\Manufacturing\JobShop\PurchasingTrait;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.*']),
    ],
    routePrefix: 'ion/bill-of-materials',
    normalizationContext: ['groups' => ['bom', 'ion:engineering:revision', 'ion:item', 'bom:purchasing']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
class PurchasingView extends BillOfMaterials
{
    use EngineeringRevisionTrait;
    use PurchasingTrait;

    #[Groups(['bom'])]
    public string $engineeringDescription;

    #[Groups(['bom'])]
    public string $engineeringSignalCode;

    #[Groups(['bom'])]
    public string $itemSignalCode;

    /**
     * @var Collection<PurchasingViewItem>
     */
    #[Groups(['bom'])]
    protected Collection $items;
}
