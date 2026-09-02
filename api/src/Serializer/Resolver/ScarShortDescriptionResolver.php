<?php

declare(strict_types=1);

namespace App\Serializer\Resolver;

use App\Entity\Quality\SupplierCorrectiveActionRequest;

class ScarShortDescriptionResolver implements ShortDescriptionResolverInterface
{
    public function supports(object $resource): bool
    {
        return $resource instanceof SupplierCorrectiveActionRequest;
    }

    public function resolve(object $resource): ?string
    {
        return $resource->shortDescription;
    }
}
