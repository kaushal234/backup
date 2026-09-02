<?php

declare(strict_types=1);

namespace App\Link\ResourceSourceProvider\Support;

use App\Entity\EmissionRating;
use App\Link\ResourceSourceProvider\ResourceSourceProviderInterface;

class EmissionRatingResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getService(): string
    {
        return 'energySourceService';
    }

    public function getProperties(): string
    {
        return 'id name';
    }

    public function getReturnedFields(): string
    {
        return 'id name';
    }

    public function supports(string $class): bool
    {
        return EmissionRating::class === $class;
    }
}
