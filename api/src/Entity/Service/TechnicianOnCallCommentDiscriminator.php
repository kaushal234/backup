<?php

declare(strict_types=1);

namespace App\Entity\Service;

enum TechnicianOnCallCommentDiscriminator
{
    case OPEN_FACTORY_FLAG;
    case CLOSE_FACTORY_FLAG;
}
