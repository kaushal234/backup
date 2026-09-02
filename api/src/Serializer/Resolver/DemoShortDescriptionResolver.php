<?php

declare(strict_types=1);

namespace App\Serializer\Resolver;

use App\Entity\Sales\Demo;

class DemoShortDescriptionResolver implements ShortDescriptionResolverInterface
{
    public function supports(object $resource): bool
    {
        return $resource instanceof Demo;
    }

    public function resolve(object $resource): ?string
    {
        $customerName = $resource->getCustomer()->getName();
        $serialNumber = $resource->getEquipmentRecord()?->getSerialNumber();

        return $serialNumber ? \sprintf('%s - %s', $customerName, $serialNumber) : $customerName;
    }
}
