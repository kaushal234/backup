<?php

declare(strict_types=1);

namespace App\Link\ResourceSourceProvider\Directory;

use App\Entity\Directory\Location;
use App\Link\ResourceSourceProvider\AbstractResourceSourceProvider;

class LocationResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function getService(): string
    {
        return 'organizationService';
    }

    public function supports(string $class): bool
    {
        return Location::class === $class;
    }
}
