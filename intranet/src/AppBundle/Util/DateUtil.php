<?php

declare(strict_types=1);

namespace AppBundle\Util;

class DateUtil
{
    public static function secondToDateInterval(float $seconds): \DateInterval
    {
        // Use @ on DateTime parameter to indicate the time send is a timestamp
        $dateStart = new \DateTime('@0');
        $dateEnd = new \DateTime("@$seconds");

        // We calculate the diff between timestamp 0 and timestamp with our seconds to create a DateInterval
        return $dateStart->diff($dateEnd);
    }
}
