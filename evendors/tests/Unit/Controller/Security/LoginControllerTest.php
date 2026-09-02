<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Security;

use App\Controller\Security\LoginController;
use App\Http\Responder;
use App\Security\Security;
use App\Security\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

/**
 * @group unit
 * @group legacy
 */
final class LoginControllerTest extends TestCase
{
    private Environment&MockObject $twig;
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private TokenStorageInterface&MockObject $tokenStorage;
    private LoginController $controller;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $responder = new Responder($this->twig, $this->urlGenerator, $this->createMock(SerializerInterface::class), new RequestStack());
        $security = new Security($this->createMock(AuthorizationCheckerInterface::class), $this->tokenStorage);

        $this->controller = new LoginController($security, $responder);
    }

    public function testNonAuthenticated(): void
    {
        $this->tokenStorage->expects($this->once())->method('getToken')->willReturn(null);

        $error = new AuthenticationException();
        $authenticationUtils = $this->createMock(AuthenticationUtils::class);
        $authenticationUtils->expects($this->once())->method('getLastAuthenticationError')->willReturn($error);
        $authenticationUtils->expects($this->once())->method('getLastUsername')->willReturn('saif');

        $this->twig->expects($this->once())->method('render')->with('security/login.html.twig', ['last_username' => 'saif', 'error' => $error])->willReturn('body');

        $response = ($this->controller)($authenticationUtils);

        self::assertSame('body', $response->getContent());
    }

    public function testAnonymouslyAuthenticated(): void
    {
        $token = new NullToken();

        $this->tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $error = new AuthenticationException();
        $authenticationUtils = $this->createMock(AuthenticationUtils::class);
        $authenticationUtils->expects($this->once())->method('getLastAuthenticationError')->willReturn($error);
        $authenticationUtils->expects($this->once())->method('getLastUsername')->willReturn('saif');

        $this->twig->expects($this->once())->method('render')->with('security/login.html.twig', ['last_username' => 'saif', 'error' => $error])->willReturn('body');

        $response = ($this->controller)($authenticationUtils);

        self::assertSame('body', $response->getContent());
    }

    public function testFullyAuthenticated(): void
    {
        $user = new User\User(1, 'Saif Eddin', 'Gmati', 'saif@les-tilleuls.coop', '123456789', address: new User\Address(
            firstLine: 'Foo',
            secondLine: 'Bar',
            city: 'Nabeul',
            state: 'Nabeul',
            country: 'Tunisia',
            zipCode: '8000'
        ));
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        $this->tokenStorage->expects($this->once())->method('getToken')->willReturn($token);
        $this->urlGenerator->expects($this->once())->method('generate')->with('index', [])->willReturn('/');

        $authenticationUtils = $this->createMock(AuthenticationUtils::class);
        $authenticationUtils->expects($this->never())->method('getLastAuthenticationError');
        $authenticationUtils->expects($this->never())->method('getLastUsername');

        $response = ($this->controller)($authenticationUtils);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/', $response->getTargetUrl());
    }
}
