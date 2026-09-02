<?php

declare(strict_types=1);

namespace Unit\Security;

use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use App\Sdk\Http\Client;
use App\Security\Authentication\Authenticator;
use App\Security\User\User;
use App\Security\User\UserProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;

/**
 * @group unit
 */
class AuthenticatorTest extends TestCase
{
    /**
     * @dataProvider redirectProvider
     *
     * @param string[] $expectedFlashWarning
     */
    public function testOnAuthenticationSuccessRedirect(
        object $user,
        ?string $sessionTargetPath,
        int $expectedGenerateCalls,
        int $expectedSetTokenCalls,
        string $expectedTargetUrl,
        array $expectedFlashWarning
    ): void {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnCallback(static function ($name) {
            return match ($name) {
                'security:password-reset:index' => '/password-reset',
                'index' => '/index',
                default => '/',
            };
        });

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $query = $this->createMock(QueryBusInterface::class);
        $httpClient = $this->createMock(HttpClientInterface::class);
        $client = new Client($httpClient);
        $userProvider = new UserProvider($client, $query, new RequestStack());
        $responder = new Responder($this->createMock(Environment::class), $urlGenerator, new RequestStack());

        $authenticator = new Authenticator($responder, $urlGenerator, $userProvider, $client, $tokenStorage);

        $tokenStorage->expects($this->exactly($expectedSetTokenCalls))
            ->method('setToken')
            ->with($this->logicalOr($this->isNull(), $this->anything()));

        $session = new Session(new MockArraySessionStorage());
        if (null !== $sessionTargetPath) {
            $session->set('_security_.target_path', $sessionTargetPath);
        }

        $request = $this->createMock(Request::class);
        $request->method('getSession')->willReturn($session);
        $request->request = new InputBag();

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $response = $authenticator->onAuthenticationSuccess($request, $token, '');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($expectedTargetUrl, $response->getTargetUrl());

        $this->assertSame($expectedFlashWarning, $session->getFlashBag()->get('warning'));
    }

    public function redirectProvider(): \Generator
    {
        yield 'password_expired' => [
            new User(42, 'John', 'Doe', 'johndoe', 'token', '2000-01-01', 'en', []),
            null,
            1,
            1,
            '/password-reset',
            ['Your password has expired.'],
        ];

        yield 'not_expired_with_target' => [
            new User(43, 'Alice', 'Smith', 'alicesmith', 'token2', (new \DateTime('+1 day'))->format('Y-m-d'), 'en', []),
            '/some/target',
            0,
            0,
            '/index',
            [],
        ];
    }

    /**
     * @dataProvider responderProvider
     *
     * @param string[] $expectedFlashWarning
     */
    public function testOnAuthenticationSuccessResponder(
        User $user,
        ?string $sessionTargetPath,
        int $expectedGenerateCalls,
        int $expectedSetTokenCalls,
        array $expectedFlashWarning
    ): void {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnCallback(static fn ($name) => 'security:password-reset:index' === $name ? '/password-reset' : '/index');

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $query = $this->createMock(QueryBusInterface::class);
        $httpClient = $this->createMock(HttpClientInterface::class);
        $client = new Client($httpClient);
        $userProvider = new UserProvider($client, $query, new RequestStack());
        $responder = new Responder($this->createMock(Environment::class), $urlGenerator, new RequestStack());

        $authenticator = new Authenticator($responder, $urlGenerator, $userProvider, $client, $tokenStorage);

        $tokenStorage->expects($this->exactly($expectedSetTokenCalls))
            ->method('setToken')
            ->with($this->logicalOr($this->isNull(), $this->anything()));

        $session = new Session(new MockArraySessionStorage());
        if (null !== $sessionTargetPath) {
            $session->set('_security_.target_path', $sessionTargetPath);
        }

        $request = $this->createMock(Request::class);
        $request->method('getSession')->willReturn($session);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->assertSame($expectedFlashWarning, $session->getFlashBag()->get('warning'));
    }

    public function responderProvider(): \Generator
    {
        yield 'not_expired_no_target' => [
            new User(44, 'Bob', 'Brown', 'bobbrown', 'token3', (new \DateTime('+1 day'))->format('Y-m-d'), 'en', []),
            null,
            0,
            0,
            [],
        ];
    }
}
