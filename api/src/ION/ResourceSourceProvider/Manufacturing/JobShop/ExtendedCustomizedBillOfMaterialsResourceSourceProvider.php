<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\Manufacturing\JobShop;

use App\ION\Resources\Manufacturing\JobShop\ExtendedCustomizedBillOfMaterials;

class ExtendedCustomizedBillOfMaterialsResourceSourceProvider extends CustomizedBillOfMaterialsResourceSourceProvider
{
    public function getItemReadOperation(): ?string
    {
        return 'txListExtended';
    }

    public function supports(string $class): bool
    {
        return ExtendedCustomizedBillOfMaterials::class === $class;
    }
}
