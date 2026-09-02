<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use Doctrine\Common\Collections\Collection;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.*']),
    ],
    routePrefix: 'ion/customized-bill-of-materials',
    normalizationContext: ['groups' => ['cbom', 'ion:pmoc', 'ion:item']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
#[ApiFilter(DataAreaFilter::class, properties: ['date', 'otherLanguage', 'signalCodeFilter', 'signalCodeFilterMethod', 'signalCodeAttribute'])]
class Manuals extends CustomizedBillOfMaterials
{
    /**
     * @var Collection<ManualPart>
     */
    protected Collection $items;
}
