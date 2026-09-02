<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Http\Responder;
use App\Security\AccessDeniedHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

/**
 * @group unit
 */
final class AccessDeniedHandlerTest extends TestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private RequestStack&MockObject $requestStack;
    private AccessDeniedHandler $handler;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $responder = new Responder($this->createMock(Environment::class), $this->urlGenerator, $this->requestStack);
        $this->handler = new AccessDeniedHandler($responder);
    }

    public function testHandleFlashesErrorAndRedirectsToIndex(): void
    {
        $request = $this->createMock(Request::class);
        $session = $this->createMock(Session::class);
        $bag = $this->createMock(FlashBagInterface::class);

        $this->requestStack->expects($this->once())->method('getCurrentRequest')->willReturn($request);
        $request->expects($this->once())->method('getSession')->willReturn($session);
        $session->expects($this->once())->method('getFlashBag')->willReturn($bag);
        $bag->expects($this->once())->method('add')->with('danger', 'security.warning.access_denied');

        $this->urlGenerator->expects($this->once())->method('generate')->with('index')->willReturn('/');

        $response = $this->handler->handle(new Request(), new AccessDeniedException());

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertSame('/', $response->getTargetUrl());
    }

    public function testHandleRedirectsToRefererWhenAvailable(): void
    {
        $this->mockFlashBag();

        $this->urlGenerator->expects($this->never())->method('generate');

        $request = Request::create('https://extranet.test/technician-on-calls/new');
        $request->headers->set('referer', 'https://extranet.test/technician-on-calls');

        $response = $this->handler->handle($request, new AccessDeniedException());

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertSame('https://extranet.test/technician-on-calls', $response->getTargetUrl());
    }

    public function testHandleIgnoresRefererFromAnotherHost(): void
    {
        $this->mockFlashBag();

        $this->urlGenerator->expects($this->once())->method('generate')->with('index')->willReturn('/');

        $request = Request::create('https://extranet.test/technician-on-calls/new');
        $request->headers->set('referer', 'https://evil.test/phishing');

        $response = $this->handler->handle($request, new AccessDeniedException());

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/', $response->getTargetUrl());
    }

    public function testHandleIgnoresRefererPointingToTheDeniedRequest(): void
    {
        $this->mockFlashBag();

        $this->urlGenerator->expects($this->once())->method('generate')->with('index')->willReturn('/');

        $request = Request::create('https://extranet.test/technician-on-calls/new');
        $request->headers->set('referer', 'https://extranet.test/technician-on-calls/new');

        $response = $this->handler->handle($request, new AccessDeniedException());

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/', $response->getTargetUrl());
    }

    private function mockFlashBag(): void
    {
        $request = $this->createMock(Request::class);
        $session = $this->createMock(Session::class);
        $bag = $this->createMock(FlashBagInterface::class);

        $this->requestStack->expects($this->once())->method('getCurrentRequest')->willReturn($request);
        $request->expects($this->once())->method('getSession')->willReturn($session);
        $session->expects($this->once())->method('getFlashBag')->willReturn($bag);
        $bag->expects($this->once())->method('add')->with('danger', 'security.warning.access_denied');
    }
}
