<?php

declare(strict_types=1);

namespace App\Serializer\Resolver;

use App\Entity\MinutesOfMeeting\Meeting;

class MeetingShortDescriptionResolver implements ShortDescriptionResolverInterface
{
    public function supports(object $resource): bool
    {
        return $resource instanceof Meeting;
    }

    public function resolve(object $resource): ?string
    {
        return $resource->getTitle();
    }
}
