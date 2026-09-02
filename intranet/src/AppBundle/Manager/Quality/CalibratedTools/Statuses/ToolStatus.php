<?php

declare(strict_types=1);

namespace AppBundle\Manager\Quality\CalibratedTools\Statuses;

final class ToolStatus
{
    public const ACTIVE = 'ACTIVE';
    public const CALIBRATION_DUE_SOON = 'CALIBRATION_DUE_SOON';
    public const EXPIRED = 'EXPIRED';
    public const UNDER_CALIBRATION = 'UNDER_CALIBRATION';
    public const OUT_OF_SERVICE = 'OUT_OF_SERVICE';
    public const SCRAPPED = 'SCRAPPED';

    public static function getStatuses()
    {
        return [
            self::ACTIVE => self::ACTIVE,
            self::CALIBRATION_DUE_SOON => self::CALIBRATION_DUE_SOON,
            self::EXPIRED => self::EXPIRED,
            self::UNDER_CALIBRATION => self::UNDER_CALIBRATION,
            self::OUT_OF_SERVICE => self::OUT_OF_SERVICE,
        ];
    }

    public static function getDefaultStatuses()
    {
        return [
            self::OUT_OF_SERVICE => self::OUT_OF_SERVICE,
        ];
    }
}
