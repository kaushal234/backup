<?php

declare(strict_types=1);

namespace App\Report\Handler;

trait DefaultPriorityTrait
{
    public static function getDefaultPriority(): int
    {
        return 0;
    }
}
