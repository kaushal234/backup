<?php

declare(strict_types=1);

namespace App\SageParts\ResourceSourceProvider;

use App\SageParts\Resources\SagePart;

class SagePartResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return 'PriceAndAvailabilityRequest';
    }

    public function supports(string $class): bool
    {
        return SagePart::class === $class;
    }
}
