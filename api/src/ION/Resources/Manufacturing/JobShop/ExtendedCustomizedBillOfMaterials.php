<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\ION\DataProvider\CachedIONItemDataProvider;

/**
 * @deprecated
 */
#[ApiResource(
    operations: [
        new Get(
            requirements: ['id' => '.*'],
            security: "is_granted('BILL_OF_MATERIAL_VENDOR_VOTER', object)",
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['cbom', 'ion:text']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
class ExtendedCustomizedBillOfMaterials extends CustomizedBillOfMaterials
{
}
