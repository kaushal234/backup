<?php

declare(strict_types=1);

namespace Unit\Security;

use App\CQRS\QueryBusInterface;
use App\Sdk\Http\Client;
use App\Security\Authentication\SwitchUserAuthenticator;
use App\Security\User;
use App\Security\User\UserProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @group unit
 */
class SwitchUserAuthenticatorTest extends KernelTestCase
{
    private SwitchUserAuthenticator $authenticator;
    private RouterInterface&MockObject $routerMock;

    protected function setUp(): void
    {
        $queryBusMock = $this->createMock(QueryBusInterface::class);
        $httpClient = $this->createMock(HttpClientInterface::class);
        $client = new Client($httpClient);
        $userProvider = new UserProvider($client, $queryBusMock, new RequestStack());
        $this->routerMock = $this->createMock(RouterInterface::class);

        $this->authenticator = new SwitchUserAuthenticator(
            $this->createMock(UrlGeneratorInterface::class),
            $userProvider,
            $this->routerMock,
            $this->createMock(Security::class),
        );
    }

    public function testOnAuthenticationSuccess(): void
    {
        $request = new Request();
        $user = new User\User(
            id: 1,
            firstname: 'Saif Eddin',
            lastname: 'Gmati',
            identifier: 'saif@les-tilleuls.coop',
            token: '123456789',
            passwordExpirationDate: new \DateTime('+30 days')->format('Y-m-d'),
            language: 'en',
            acls: [],
        );

        $token = new UsernamePasswordToken($user, 'main', ['ROLE_USER']);
        $expectedRedirectUrl = '/';
        $this->routerMock->method('generate')->with('index')->willReturn($expectedRedirectUrl);
        $response = $this->authenticator->onAuthenticationSuccess($request, $token, 'main');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($expectedRedirectUrl, $response->getTargetUrl());
    }

    /**
     * @dataProvider requestProvider
     */
    public function testSupports(Request $request, bool $expectedResult): void
    {
        $this->assertSame($expectedResult, $this->authenticator->supports($request));
    }

    public function testAuthenticate(): void
    {
        $request = Request::create(SwitchUserAuthenticator::SWITCH_PARAMETER, 'GET', ['_userIdentifier' => 'foo'], [SwitchUserAuthenticator::JWT_COOKIE => 'un beau cookie']);
        $this->authenticator->supports($request);
        $result = $this->authenticator->authenticate($request);
        $this->assertTrue($result->hasBadge(UserBadge::class));
        $this->assertSame('foo', $result->getBadge(UserBadge::class)->getUserIdentifier());
    }

    public function requestProvider(): \Generator
    {
        $request1 = Request::create(SwitchUserAuthenticator::SWITCH_PARAMETER, 'GET', ['_userIdentifier' => 'foo'], [SwitchUserAuthenticator::JWT_COOKIE => 'un beau cookie']);
        $request2 = Request::create('/not_switch_uri', 'GET', ['_userIdentifier' => 'foo'], [SwitchUserAuthenticator::JWT_COOKIE => 'un beau cookie']);
        $request3 = Request::create(SwitchUserAuthenticator::SWITCH_PARAMETER, 'POST', ['_userIdentifier' => 'foo'], [SwitchUserAuthenticator::JWT_COOKIE => 'un beau cookie']);
        $request4 = Request::create(SwitchUserAuthenticator::SWITCH_PARAMETER, 'GET', ['toto_le_haricot' => 'foo'], [SwitchUserAuthenticator::JWT_COOKIE => 'un beau cookie']);
        $request5 = Request::create(SwitchUserAuthenticator::SWITCH_PARAMETER, 'GET', ['_userIdentifier' => 'foo'], ['moi_je_suis_un_jwt' => 'un beau cookie']);

        yield ['test ok' => $request1, true];
        yield ['not good uri' => $request2, false];
        yield ['not GET method' => $request3, false];
        yield ['invalid query key' => $request4, false];
        yield ['invalid jwt key' => $request5, false];
    }
}
