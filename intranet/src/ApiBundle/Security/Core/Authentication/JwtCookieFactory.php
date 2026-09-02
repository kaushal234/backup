<?php

declare(strict_types=1);

namespace ApiBundle\Security\Core\Authentication;

use Symfony\Component\HttpFoundation\Cookie;

class JwtCookieFactory
{
    final public const COOKIE_NAME = '_jwt';
    private ?string $domain = null;

    public function generate(string $value): Cookie
    {
        return new Cookie(
            self::COOKIE_NAME,
            $value,
            0,
            '/',
            $this->domain,
            true,
            false,
            false,
            Cookie::SAMESITE_NONE
        );
    }

    public function setDomainFromRequestHttpHost(string $httpHost): void
    {
        $this->domain = implode('.', \array_slice(explode('.', $httpHost), -2, 2));
    }
}
