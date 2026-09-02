<?php

declare(strict_types=1);

namespace App\Serializer\Resolver;

use App\Entity\BaseTask;

class BaseTaskShortDescriptionResolver implements ShortDescriptionResolverInterface
{
    public function supports(object $resource): bool
    {
        return $resource instanceof BaseTask;
    }

    public function resolve(object $resource): ?string
    {
        return $resource->shortDescription;
    }
}
