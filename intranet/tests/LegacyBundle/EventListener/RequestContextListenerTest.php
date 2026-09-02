<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\EventListener;

use App\Kernel;
use LegacyBundle\EventListener\RequestContextListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RequestContext;

class RequestContextListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testQueryParameterMIsDuplicatedForAPOSTRequest()
    {
        $request = new Request([], [], [], [], [], [], null);
        $request->setMethod(Request::METHOD_POST);

        $listener = $this->getListener($request);
        $listener->onKernelRequest(new RequestEvent(new Kernel('pouet', true), $request, 1));

        self::assertSame(['mst', 'new'], $request->query->all()['m'] ?? []);
    }

    public function testQueryParameterMIsNotDuplicatedForNonPOSTRequest()
    {
        foreach ([Request::METHOD_PUT, Request::METHOD_DELETE, Request::METHOD_GET] as $method) {
            $request = new Request([], [], [], [], [], [], null);
            $request->setMethod($method);

            $listener = $this->getListener($request);
            $listener->onKernelRequest(new RequestEvent(new Kernel('pouet', true), $request, 1));

            self::assertEmpty($request->query->all()['m'] ?? []);
        }
    }

    public function testQueryParameterMIsNotOverriddenIfExisting()
    {
        $request = new Request([], [], [], [], [], [], null);
        $request->setMethod(Request::METHOD_POST);
        $request->query->set('m', $m = ['php', 'lol']);

        $listener = $this->getListener($request);
        $listener->onKernelRequest(new RequestEvent(new Kernel('pouet', true), $request, 1));

        self::assertSame($m, $request->query->all()['m'] ?? []);
    }

    private function getListener(Request $request)
    {
        $request->request->set('m', ['mst', 'new']);

        $requestContextProphecy = $this->prophesize(RequestContext::class);
        $requestContextProphecy->fromRequest($request)->shouldBeCalledTimes(1)->willReturn($requestContextProphecy->reveal());

        return new RequestContextListener($requestContextProphecy->reveal());
    }
}
