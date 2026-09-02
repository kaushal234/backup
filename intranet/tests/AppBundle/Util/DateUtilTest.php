<?php

declare(strict_types=1);

namespace AppBundle\Util;

use PHPUnit\Framework\TestCase;

class DateUtilTest extends TestCase
{
    public function testSecondToDateInterval()
    {
        $interval = DateUtil::secondToDateInterval(123456789);
        self::assertSame(3, $interval->y);
        self::assertSame(10, $interval->m);
        self::assertSame(28, $interval->d);
        self::assertSame(21, $interval->h);
        self::assertSame(33, $interval->i);
        self::assertSame(9, $interval->s);
        self::assertSame(1428, $interval->days);
    }
}
