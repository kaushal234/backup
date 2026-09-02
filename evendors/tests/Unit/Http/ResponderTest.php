<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http;

use App\Http\Responder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psl\Collection\Map;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

/**
 * @group unit
 */
final class ResponderTest extends TestCase
{
    private Responder $responder;
    private Environment&MockObject $twig;
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private SerializerInterface&MockObject $serializer;
    private RequestStack&MockObject $requestStack;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->responder = new Responder($this->twig, $this->urlGenerator, $this->serializer, $this->requestStack);
    }

    public function testFlash(): void
    {
        $request = $this->createMock(Request::class);
        $session = $this->createMock(Session::class);
        $bag = $this->createMock(FlashBagInterface::class);

        $this->requestStack->expects($this->once())->method('getCurrentRequest')->willReturn($request);
        $request->expects($this->once())->method('getSession')->willReturn($session);
        $session->expects($this->once())->method('getFlashBag')->willReturn($bag);

        $bag->expects($this->once())->method('add')->with('scary', 'Boo!');

        $this->responder->flash('scary', 'Boo!');
    }

    public function testEmptyResponse(): void
    {
        $response = $this->responder->empty(Response::HTTP_ACCEPTED, ['X-Foo' => ['bar', 'baz']]);

        self::assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
        self::assertTrue($response->headers->has('X-Foo'));
        self::assertSame('bar', $response->headers->get('X-Foo'));
        self::assertSame(['bar', 'baz'], $response->headers->all('X-Foo'));
    }

    public function testRender(): void
    {
        $this->twig->expects($this->once())->method('render')->with('foo.html.twig', ['foo' => 'bar'])->willReturn('body');

        $response = $this->responder->render('foo.html.twig', ['foo' => 'bar'], Response::HTTP_ACCEPTED, ['X-Foo' => ['bar', 'baz']]);

        self::assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
        self::assertTrue($response->headers->has('X-Foo'));
        self::assertSame('bar', $response->headers->get('X-Foo'));
        self::assertSame(['bar', 'baz'], $response->headers->all('X-Foo'));
        self::assertSame('body', $response->getContent());
        self::assertTrue($response->headers->has('Content-Type'));
        self::assertSame('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
    }

    public function testRenderDoesNotOverrideContentType(): void
    {
        $this->twig->expects($this->once())->method('render')->with('foo.html.twig', ['foo' => 'bar'])->willReturn('body');

        $response = $this->responder->render('foo.html.twig', ['foo' => 'bar'], headers: ['Content-Type' => 'foo']);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('body', $response->getContent());
        self::assertTrue($response->headers->has('Content-Type'));
        self::assertSame('foo', $response->headers->get('Content-Type'));
    }

    public function testRedirect(): void
    {
        $response = $this->responder->redirect('https://les-tilleuls.coop', Response::HTTP_SEE_OTHER, ['X-Foo' => ['bar', 'baz']]);

        self::assertSame(Response::HTTP_SEE_OTHER, $response->getStatusCode());
        self::assertTrue($response->headers->has('X-Foo'));
        self::assertSame('bar', $response->headers->get('X-Foo'));
        self::assertSame(['bar', 'baz'], $response->headers->all('X-Foo'));
        self::assertSame('https://les-tilleuls.coop', $response->getTargetUrl());
    }

    public function testRoute(): void
    {
        $this->urlGenerator->expects($this->once())->method('generate')->with('les-tilleuls', ['secure' => true])->willReturn('https://les-tilleuls.coop');
        $response = $this->responder->route('les-tilleuls', ['secure' => true], Response::HTTP_SEE_OTHER, ['X-Foo' => ['bar', 'baz']]);

        self::assertSame(Response::HTTP_SEE_OTHER, $response->getStatusCode());
        self::assertTrue($response->headers->has('X-Foo'));
        self::assertSame('bar', $response->headers->get('X-Foo'));
        self::assertSame(['bar', 'baz'], $response->headers->all('X-Foo'));
        self::assertSame('https://les-tilleuls.coop', $response->getTargetUrl());
    }

    public function testJson(): void
    {
        $object = new Map([]);

        $this->serializer->expects($this->once())->method('serialize')->with($object, 'json', [
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            'foo' => 'bar',
        ])->willReturn('{"foo": "bar"}');

        $response = $this->responder->json($object, Response::HTTP_ACCEPTED, ['X-Foo' => ['bar', 'baz']], ['foo' => 'bar']);

        self::assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
        self::assertTrue($response->headers->has('X-Foo'));
        self::assertSame('bar', $response->headers->get('X-Foo'));
        self::assertSame(['bar', 'baz'], $response->headers->all('X-Foo'));
        self::assertSame('{"foo": "bar"}', $response->getContent());
    }
}
