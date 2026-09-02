<?php

declare(strict_types=1);

namespace Tests\ApiBundle\Form\DataTransformer;

use ApiBundle\Form\DataTransformer\DateTimeTransformer;
use PHPUnit\Framework\TestCase;

class DateTimeTransformerTest extends TestCase
{
    public function testDefaultBehavior()
    {
        $transformer = new DateTimeTransformer();

        self::assertNull($transformer->transform(''));
        $now = new \DateTime();
        self::assertSame($now, $transformer->transform($now));
        self::assertInstanceOf(\DateTime::class, $result = $transformer->transform('2009-12-18T14:10:00-05:00'));
        self::assertSame('2009-12-18T14:10:00-05:00', $result->format(\DATE_ATOM));
        self::assertSame('2009-12-18T14:10:00-05:00', $transformer->reverseTransform($result));
        self::assertNull($transformer->reverseTransform('foo'));
    }

    public function testCustomFormat()
    {
        $transformer = new DateTimeTransformer('H:i');

        self::assertSame('14:10', $transformer->reverseTransform(new \DateTime('2009-12-18T14:10:02-05:00')));
    }

    public function testTimeZone()
    {
        $transformer = new DateTimeTransformer(null, new \DateTimeZone('Europe/Paris'));

        $result = $transformer->transform('2009-12-18T14:10:02-05:00');
        self::assertSame('Europe/Paris', $result->getTimezone()->getName());
        self::assertSame('2009-12-18T20:10:02+01:00', $result->format(\DATE_ATOM));
    }
}
