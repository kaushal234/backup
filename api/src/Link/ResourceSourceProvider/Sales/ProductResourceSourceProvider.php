<?php

declare(strict_types=1);

namespace App\Link\ResourceSourceProvider\Sales;

use App\Entity\Sales\Product;
use App\Link\ResourceSourceProvider\AbstractResourceSourceProvider;

class ProductResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function getService(): string
    {
        return 'equipmentModelService';
    }

    public function supports(string $class): bool
    {
        return Product::class === $class;
    }
}
