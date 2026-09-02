<?php

declare(strict_types=1);

namespace App\SageParts\P21\ResourceSourceProvider;

use App\SageParts\P21\Resources\Part;

class PartResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getOperation(): ?string
    {
        return 'SCAR_view_parts';
    }

    public function getFieldMapping(): array
    {
        return ['item' => 'ItemId'];
    }

    public function supports(string $class): bool
    {
        return Part::class === $class;
    }
}
