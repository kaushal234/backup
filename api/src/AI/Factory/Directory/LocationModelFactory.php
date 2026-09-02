<?php

declare(strict_types=1);

namespace App\AI\Factory\Directory;

use App\AI\Dto\Directory\LocationModel;
use App\Entity\Directory\Location;
use LegacyBundle\Entity\Directory\AbstractLocation as LegacyLocation;

final class LocationModelFactory
{
    public function create(Location|LegacyLocation $location): LocationModel
    {
        if ($location instanceof LegacyLocation) {
            return new LocationModel(
                name: $location->getName(),
                erp: $location->erp,
            );
        }

        return new LocationModel(
            name: $location->getName(),
            erp: $location->getErp(),
        );
    }
}
