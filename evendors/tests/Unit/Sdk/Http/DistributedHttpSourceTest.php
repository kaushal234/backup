<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\Http;

use App\Sdk\Http\DistributedHttpSource;
use App\Sdk\Http\HttpSource;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
final class DistributedHttpSourceTest extends TestCase
{
    public function testCombine(): void
    {
        $distribution = DistributedHttpSource::combine(
            HttpSource::create('GET', '/foo'),
            HttpSource::create('GET', '/bar'),
            HttpSource::create('GET', '/baz'),
        );

        self::assertCount(3, $distribution->sources);
        self::assertSame('GET', $distribution->sources[0]->method);
        self::assertSame('GET', $distribution->sources[1]->method);
        self::assertSame('GET', $distribution->sources[2]->method);
        self::assertSame('/foo', $distribution->sources[0]->uri);
        self::assertSame('/bar', $distribution->sources[1]->uri);
        self::assertSame('/baz', $distribution->sources[2]->uri);
    }

    public function testCombineWithNamedParameters(): void
    {
        $distribution = DistributedHttpSource::combine(
            foo: HttpSource::create('GET', '/foo'),
            bar: HttpSource::create('GET', '/bar'),
            baz: HttpSource::create('GET', '/baz'),
        );

        self::assertCount(3, $distribution->sources);
        self::assertSame('GET', $distribution->sources[0]->method);
        self::assertSame('GET', $distribution->sources[1]->method);
        self::assertSame('GET', $distribution->sources[2]->method);
        self::assertSame('/foo', $distribution->sources[0]->uri);
        self::assertSame('/bar', $distribution->sources[1]->uri);
        self::assertSame('/baz', $distribution->sources[2]->uri);
    }
}
