<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\Http;

use App\Sdk\Http\HttpSource;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
final class HttpSourceTest extends TestCase
{
    public function testCreate(): void
    {
        $source = HttpSource::create('GET', '/foo', [
            'bar' => 'baz',
        ]);

        self::assertSame('GET', $source->method);
        self::assertSame('/foo', $source->uri);
        self::assertSame(['bar' => 'baz'], $source->options);
    }
}
