<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\CountryPhonePrefixResolver;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
final class CountryPhonePrefixResolverTest extends TestCase
{
    private CountryPhonePrefixResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new CountryPhonePrefixResolver();
    }

    public function testItResolvesKnownCountries(): void
    {
        self::assertSame('+33', $this->resolver->resolve('FR'));
        self::assertSame('+1', $this->resolver->resolve('US'));
    }

    public function testItIsCaseInsensitive(): void
    {
        self::assertSame('+33', $this->resolver->resolve('fr'));
    }

    public function testItReturnsNullForEmptyOrNullInput(): void
    {
        self::assertNull($this->resolver->resolve(null));
        self::assertNull($this->resolver->resolve(''));
    }

    public function testItReturnsNullForUnknownRegion(): void
    {
        self::assertNull($this->resolver->resolve('ZZ'));
    }
}
