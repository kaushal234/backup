<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Sdk\Http\ClientInterface;
use App\Security\Authentication\SwitchUserAuthenticator;
use App\Security\User;
use App\Security\User\UserProvider;
use App\Test\Helpers\ContainerTrait;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

/**
 * @group unit
 */
class SwitchUserAuthenticatorTest extends KernelTestCase
{
    use ContainerTrait;

    private SwitchUserAuthenticator $authenticator;
    private RouterInterface $routerMock;

    protected function setUp(): void
    {
        $userProvider = new UserProvider($this->createMock(ClientInterface::class));
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
        $user = new User\User(1, 'Saif Eddin', 'Gmati', 'saif@les-tilleuls.coop', '123456789', address: new User\Address(
            firstLine: 'Foo',
            secondLine: 'Bar',
            city: 'Nabeul',
            state: 'Nabeul',
            country: 'Tunisia',
            zipCode: '8000'
        ));

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
        $this->assertInstanceOf(Passport::class, $result);
        $this->assertTrue($result->hasBadge(UserBadge::class));
        $this->assertSame('foo', $result->getBadge(UserBadge::class)->getUserIdentifier());
    }

    public function requestProvider(): Generator
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
