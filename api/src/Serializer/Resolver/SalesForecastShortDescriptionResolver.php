<?php

declare(strict_types=1);

namespace App\Serializer\Resolver;

use App\Entity\Sales\SalesForecast;

class SalesForecastShortDescriptionResolver implements ShortDescriptionResolverInterface
{
    public function supports(object $resource): bool
    {
        return $resource instanceof SalesForecast;
    }

    public function resolve(object $resource): ?string
    {
        $buyerName = $resource->getBuyer()?->getName();
        $productName = $resource->getProduct()?->getName();

        return implode(' - ', array_filter([$buyerName, $productName])) ?: null;
    }
}
