<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\Doctrine\Transformer;

use LegacyBundle\Doctrine\Transformer\ArrayToString;
use PHPUnit\Framework\TestCase;

class ArrayToStringTest extends TestCase
{
    public function provider(): \Generator
    {
        yield 'empty array' => ['', []];
        yield 'not an array' => ['', ''];
        yield 'array' => ['foo,bar,baz', ['foo', 'bar', 'baz']];
        yield 'array with glue' => ['foo,glue,bar,glue,baz', ['foo', 'bar', 'baz'], ['glue' => ',glue,']];
    }

    /** @dataProvider provider */
    public function testTransformer(string $expected, $input, $options = [])
    {
        self::assertSame($expected, (new ArrayToString())($input, $options));
    }
}
