<?php

declare(strict_types=1);

namespace Tests\ApiBundle\Security\Core\Authentication;

use ApiBundle\Security\Core\Authentication\JwtCookieFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;

class JwtCookieFactoryTest extends TestCase
{
    public function testCookieGeneretion()
    {
        $cookie = (new JwtCookieFactory())->generate('foo');

        self::assertSame('_jwt', $cookie->getName());
        self::assertSame('foo', $cookie->getValue());
        self::assertSame('/', $cookie->getPath());
        self::assertSame(0, $cookie->getExpiresTime());
        self::assertNull($cookie->getDomain());
        // Important part to be used via JS apps
        self::assertFalse($cookie->isHttpOnly());
        self::assertTrue($cookie->isSecure());
        self::assertSame('none', $cookie->getSameSite());
    }

    public function testGenerateCookieWithDomain(): void
    {
        $cookieFactory = new JwtCookieFactory();
        $cookieFactory->setDomainFromRequestHttpHost('www.toto.example.com');
        $cookie = $cookieFactory->generate('mocked_token');
        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertSame('example.com', $cookie->getDomain());
    }
}
