<?php

declare(strict_types=1);

namespace App\Test\Helpers;

use Symfony\Component\HttpFoundation\Response;

trait BrowserKitAssertionsTrait
{
    /**
     * Shortcut for login redirection assertion.
     */
    public static function assertResponseRedirectsToLogin(): void
    {
        self::assertResponseRedirects('/security/login');
    }

    public static function assertResponseStatusNotFound(): void
    {
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
