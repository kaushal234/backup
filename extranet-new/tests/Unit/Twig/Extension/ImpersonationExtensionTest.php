<?php

declare(strict_types=1);

namespace App\Tests\Twig\Extension;

use App\Twig\Extension\ImpersonationExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ImpersonationExtensionTest extends TestCase
{
    public function testReturnsFalseWhenNoCookie(): void
    {
        $ext = $this->extensionWithCookie(null);

        self::assertFalse($ext->isImpersonating());
    }

    public function testReturnsFalseWhenCookieIsMalformed(): void
    {
        $ext = $this->extensionWithCookie('not-a-jwt');

        self::assertFalse($ext->isImpersonating());
    }

    public function testReturnsFalseWhenRoleIsAbsent(): void
    {
        $jwt = $this->makeJwt(['roles' => ['ROLE_USER', 'ROLE_PASSWORD_NOT_EXPIRED']]);
        $ext = $this->extensionWithCookie($jwt);

        self::assertFalse($ext->isImpersonating());
    }

    public function testReturnsTrueWhenImpersonatedRoleIsPresent(): void
    {
        $jwt = $this->makeJwt(['roles' => ['ROLE_USER', 'ROLE_IMPERSONATED']]);
        $ext = $this->extensionWithCookie($jwt);

        self::assertTrue($ext->isImpersonating());
    }

    public function testReturnsFalseWhenPayloadHasNoRolesKey(): void
    {
        $jwt = $this->makeJwt(['sub' => 'someone@example.com']);
        $ext = $this->extensionWithCookie($jwt);

        self::assertFalse($ext->isImpersonating());
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function makeJwt(array $payload): string
    {
        $b64url = static fn (array $data): string => mb_rtrim(
            strtr(base64_encode(json_encode($data)), '+/', '-_'),
            '='
        );

        return $b64url(['alg' => 'none']).'.'.$b64url($payload).'.signature';
    }

    private function extensionWithCookie(?string $jwt): ImpersonationExtension
    {
        $request = new Request();
        if (null !== $jwt) {
            $request->cookies->set('_jwt', $jwt);
        }

        $stack = new RequestStack();
        $stack->push($request);

        return new ImpersonationExtension($stack);
    }
}
