<?php

declare(strict_types=1);

namespace App\Util;

class DateUtil
{
    public static function diff(\DateTimeInterface $from, ?\DateTimeInterface $to): \DateInterval
    {
        if (!$to) {
            $to = new \DateTime();
        }

        return $from->diff($to);
    }
}
