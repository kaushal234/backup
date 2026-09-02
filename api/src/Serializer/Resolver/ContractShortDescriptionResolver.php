<?php

declare(strict_types=1);

namespace App\Serializer\Resolver;

use App\Entity\Legal\Contract;

class ContractShortDescriptionResolver implements ShortDescriptionResolverInterface
{
    public function supports(object $resource): bool
    {
        return $resource instanceof Contract;
    }

    public function resolve(object $resource): ?string
    {
        return $resource->shortDescription;
    }
}
