<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Directory\Location;
use App\Entity\MIS\Project\Project;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\TechnicianOnCall;

enum ModuleName: string
{
    case MIS = Project::class;
    case XU = ExtranetUser::class;
    case TOC = TechnicianOnCall::class;
    case WHT = Location::class;

    public static function getClass($value): ?string
    {
        try {
            return \constant('self::'.mb_strtoupper($value))->value;
        } catch (\Throwable $th) {
            return null;
        }
    }
}
