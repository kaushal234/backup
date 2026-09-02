<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\EventListener\StrictSearchListener;
use App\Tests\Filter\Dummies\DummyExistsFilterInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StrictSearchListenerTest extends TestCase
{
    use ProphecyTrait;

    public function unsupportedMethodsProvider()
    {
        yield [Request::METHOD_POST];
        yield [Request::METHOD_PUT];
        yield [Request::METHOD_DELETE];
    }

    /**
     * @dataProvider unsupportedMethodsProvider
     */
    public function testUnsupportedMethodsDoesnTAffectTheResponse($method): void
    {
        $request = new Request();
        $request->setMethod($method);

        $eventProphecy = $this->prophesize(RequestEvent::class);
        $eventProphecy->getRequest()->willReturn($request)->shouldBeCalledTimes(1);
        $event = $eventProphecy->reveal();

        $filterLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $listener = new StrictSearchListener($filterLocatorProphecy->reveal());
        $listener->strictSearch($event);
    }

    public function testNonResourceClassDoesnTAffectTheResponse(): void
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_GET);

        $eventProphecy = $this->prophesize(RequestEvent::class);
        $eventProphecy->getRequest()->willReturn($request)->shouldBeCalledTimes(1);
        $event = $eventProphecy->reveal();

        $filterLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $listener = new StrictSearchListener($filterLocatorProphecy->reveal());
        $listener->strictSearch($event);
    }

    public function testItemOperationResourceClassDoesnTAffectTheResponse(): void
    {
        $request = new Request([], [], ['_api_resource_class' => 'Foo']);
        $request->setMethod(Request::METHOD_GET);

        $eventProphecy = $this->prophesize(RequestEvent::class);
        $eventProphecy->getRequest()->willReturn($request)->shouldBeCalledTimes(1);
        $event = $eventProphecy->reveal();

        $filterLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $listener = new StrictSearchListener($filterLocatorProphecy->reveal());
        $listener->strictSearch($event);

        $request = new Request([], [], ['_api_resource_class' => 'Foo', '_api_operation' => new Get()]);
        $request->setMethod(Request::METHOD_GET);

        $eventProphecy = $this->prophesize(RequestEvent::class);
        $eventProphecy->getRequest()->willReturn($request)->shouldBeCalledTimes(1);
        $event = $eventProphecy->reveal();
        $listener->strictSearch($event);
    }

    public function testRequestWithoutQueryStringDoesnTAffectResponse(): void
    {
        $request = new Request([], [], ['_api_resource_class' => 'Foo', '_api_operation' => new GetCollection()]);
        $request->setMethod(Request::METHOD_GET);

        $eventProphecy = $this->prophesize(RequestEvent::class);
        $eventProphecy->getRequest()->willReturn($request)->shouldBeCalledTimes(1);
        $event = $eventProphecy->reveal();

        $filterLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $listener = new StrictSearchListener($filterLocatorProphecy->reveal());
        $listener->strictSearch($event);
    }

    public function testRequestOnInvalidProperty(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('The filter "foo" is not available for the resource "Foo".');

        $request = new Request([], [], ['_api_resource_class' => 'Foo', '_api_operation' => new GetCollection(filters: ['i_am_a_filter'])], [], [], ['QUERY_STRING' => 'foo=bar']);
        $request->setMethod(Request::METHOD_GET);

        $eventProphecy = $this->prophesize(RequestEvent::class);
        $eventProphecy->getRequest()->willReturn($request)->shouldBeCalledTimes(1);
        $event = $eventProphecy->reveal();

        $filterProphecy = $this->prophesize(FilterInterface::class);
        $filterProphecy->getDescription('Foo')->willReturn(['description' => ['property' => 'bar']])->shouldBeCalledTimes(1);

        $filterLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $filterLocatorProphecy->get('i_am_a_filter')->willReturn($filterProphecy->reveal())->shouldBeCalledTimes(1);
        $listener = new StrictSearchListener($filterLocatorProphecy->reveal(), ['baz']);
        $listener->strictSearch($event);
    }

    public function testRequestOnAllowedParamsAreAllowed(): void
    {
        $request = new Request([], [], ['_api_resource_class' => 'Foo', '_api_operation' => new GetCollection()], [], [], ['QUERY_STRING' => 'allowed_parameter=bar&allowed_array[]=foo']);
        $request->setMethod(Request::METHOD_GET);

        $eventProphecy = $this->prophesize(RequestEvent::class);
        $eventProphecy->getRequest()->willReturn($request)->shouldBeCalledTimes(1);
        $event = $eventProphecy->reveal();

        $filterLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $listener = new StrictSearchListener($filterLocatorProphecy->reveal(), ['allowed_parameter', 'allowed_array']);
        $listener->strictSearch($event);
    }

    public function testRequestWithPropertyFilterLikeParameters(): void
    {
        $request = new Request([], [], ['_api_resource_class' => 'Foo', '_api_operation' => new GetCollection(filters: ['i_am_a_filter'])], [], [], ['QUERY_STRING' => 'parameters[]=foo&parameters[bar][]=baz&parameters[fuz][fiz][]=faz&pagination=false']);
        $request->setMethod(Request::METHOD_GET);

        $eventProphecy = $this->prophesize(RequestEvent::class);
        $eventProphecy->getRequest()->willReturn($request)->shouldBeCalledTimes(1);
        $event = $eventProphecy->reveal();

        $filterProphecy = $this->prophesize(FilterInterface::class);
        $filterDescription = [
            'parameters' => [],
            'parameters[bar]' => [],
            'parameters[fuz]' => [],
            'parameters[fuz][fiz]' => [],
        ];
        $filterProphecy->getDescription('Foo')->willReturn($filterDescription)->shouldBeCalledTimes(1);

        $filterLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $filterLocatorProphecy->get('i_am_a_filter')->willReturn($filterProphecy->reveal())->shouldBeCalledTimes(1);
        $listener = new StrictSearchListener($filterLocatorProphecy->reveal(), ['pagination']);
        $listener->strictSearch($event);
    }

    public function testRequestWithNestedPropertyFilter(): void
    {
        $request = new Request([], [], ['_api_resource_class' => 'Foo', '_api_operation' => new GetCollection(filters: ['i_am_a_filter'])], [], [], ['QUERY_STRING' => 'bar.foo=bar']);
        $request->setMethod(Request::METHOD_GET);

        $eventProphecy = $this->prophesize(RequestEvent::class);
        $eventProphecy->getRequest()->willReturn($request)->shouldBeCalledTimes(1);
        $event = $eventProphecy->reveal();

        $filterProphecy = $this->prophesize(FilterInterface::class);
        $filterDescription = [
            'bar.foo' => ['property' => 'foo.bar'],
        ];
        $filterProphecy->getDescription('Foo')->willReturn($filterDescription)->shouldBeCalledTimes(1);

        $filterLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $filterLocatorProphecy->get('i_am_a_filter')->willReturn($filterProphecy->reveal())->shouldBeCalledTimes(1);
        $listener = new StrictSearchListener($filterLocatorProphecy->reveal(), ['pagination']);
        $listener->strictSearch($event);
    }

    public function testRequestWithLegacyExistsFilterSyntax(): void
    {
        $request = new Request([], [], ['_api_resource_class' => 'Foo', '_api_operation' => new GetCollection(filters: ['i_am_a_filter'])], [], [], ['QUERY_STRING' => 'bar.foo[exists]']);
        $request->setMethod(Request::METHOD_GET);

        $eventProphecy = $this->prophesize(RequestEvent::class);
        $eventProphecy->getRequest()->willReturn($request)->shouldBeCalledTimes(1);
        $event = $eventProphecy->reveal();

        $filterProphecy = $this->prophesize(DummyExistsFilterInterface::class);
        $filterDescription = [
            'exists[bar.foo]' => ['property' => 'foo.bar'],
        ];
        $filterProphecy->getDescription('Foo')->willReturn($filterDescription)->shouldBeCalledTimes(1);

        $filterLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $filterLocatorProphecy->get('i_am_a_filter')->willReturn($filterProphecy->reveal())->shouldBeCalledTimes(1);
        $listener = new StrictSearchListener($filterLocatorProphecy->reveal());
        $listener->strictSearch($event);
    }

    public function testRequestBadPropertyOnExistingFilter(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('The filter "pouet" is not available for the resource "Foo".');

        $request = new Request([], [], ['_api_resource_class' => 'Foo', '_api_operation' => new GetCollection(filters: ['i_am_a_filter'])], [], [], ['QUERY_STRING' => 'parameters=foo&pagination=false&pouet=nope']);
        $request->setMethod(Request::METHOD_GET);

        $eventProphecy = $this->prophesize(RequestEvent::class);
        $eventProphecy->getRequest()->willReturn($request)->shouldBeCalledTimes(1);
        $event = $eventProphecy->reveal();

        $filterProphecy = $this->prophesize(FilterInterface::class);
        $filterDescription = [
            'parameters' => [],
        ];
        $filterProphecy->getDescription('Foo')->willReturn($filterDescription)->shouldBeCalledTimes(1);

        $filterLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $filterLocatorProphecy->get('i_am_a_filter')->willReturn($filterProphecy->reveal())->shouldBeCalledTimes(1);
        $listener = new StrictSearchListener($filterLocatorProphecy->reveal(), ['pagination']);
        $listener->strictSearch($event);
    }
}
