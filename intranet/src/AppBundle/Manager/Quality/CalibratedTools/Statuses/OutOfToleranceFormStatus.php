<?php

declare(strict_types=1);

namespace AppBundle\Manager\Quality\CalibratedTools\Statuses;

final class OutOfToleranceFormStatus
{
    public const IN_PROGRESS = 'IN_PROGRESS';
    public const CLOSED = 'CLOSED';

    public static function getStatuses()
    {
        return [
            self::IN_PROGRESS => self::IN_PROGRESS,
            self::CLOSED => self::CLOSED,
        ];
    }
}
