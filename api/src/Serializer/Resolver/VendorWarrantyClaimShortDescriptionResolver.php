<?php

declare(strict_types=1);

namespace App\Serializer\Resolver;

use App\Entity\Purchasing\VendorWarrantyClaim;

class VendorWarrantyClaimShortDescriptionResolver implements ShortDescriptionResolverInterface
{
    public function supports(object $resource): bool
    {
        return $resource instanceof VendorWarrantyClaim;
    }

    public function resolve(object $resource): ?string
    {
        return $resource->issueOrigin;
    }
}
