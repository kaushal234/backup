<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Activity;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Event\Activity\CommentCreatedEvent;
use App\EventListener\Activity\CommentListener;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CommentListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testACommentCreationDispatchASpecificEvent()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $comment = new Comment();
        $comment->setMessage('Meow');
        $comment->setResource('/kittens/2');

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $object = new \stdClass();
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $serviceLocatorProphecy->get(IriConverterInterface::class)->shouldBeCalledTimes(1)->willReturn($iriConverterProphecy->reveal());
        $iriConverterProphecy->getResourceFromIri('/kittens/2')->shouldBeCalledTimes(1)->willReturn($object);

        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $serviceLocatorProphecy->get(EventDispatcherInterface::class)->shouldBeCalledTimes(1)->willReturn($eventDispatcherProphecy->reveal());

        $eventDispatcherProphecy->dispatch(Argument::that(static fn (CommentCreatedEvent $event) => $event->getItem() === $object && $event->getComment() === $comment && $event->isMainRequest()));

        $listener = new CommentListener($serviceLocatorProphecy->reveal());
        $listener->onCommentPost(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $comment));
    }

    public function testControllerResultMustBeAComment()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $result = new \stdClass();

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $serviceLocatorProphecy->get(IriConverterInterface::class)->shouldNotBeCalled();
        $iriConverterProphecy->getResourceFromIri(Argument::any())->shouldNotBeCalled();

        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $serviceLocatorProphecy->get(EventDispatcherInterface::class)->shouldNotBeCalled();
        $eventDispatcherProphecy->dispatch(Argument::any())->shouldNotBeCalled();

        $listener = new CommentListener($serviceLocatorProphecy->reveal());
        $listener->onCommentPost(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $result));
    }

    public function testMethodMustBePOST()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $comment = new Comment();
        $comment->setMessage('Meow');
        $comment->setResource('/kittens/2');

        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $serviceLocatorProphecy->get(IriConverterInterface::class)->shouldNotBeCalled();
        $iriConverterProphecy->getResourceFromIri(Argument::any())->shouldNotBeCalled();

        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $serviceLocatorProphecy->get(EventDispatcherInterface::class)->shouldNotBeCalled();
        $eventDispatcherProphecy->dispatch(Argument::any())->shouldNotBeCalled();

        $listener = new CommentListener($serviceLocatorProphecy->reveal());
        $listener->onCommentPost(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $comment));
    }

    public function testIfIriConverterThrowsAnExceptionNothingHappens()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $comment = new Comment();
        $comment->setMessage('Meow');
        $comment->setResource('/kittens/2');

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $serviceLocatorProphecy->get(IriConverterInterface::class)->shouldBeCalledTimes(1)->willReturn($iriConverterProphecy->reveal());
        $iriConverterProphecy->getResourceFromIri('/kittens/2')->shouldBeCalledTimes(1)->willThrow(new \Exception('whoopsy'));

        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $serviceLocatorProphecy->get(EventDispatcherInterface::class)->shouldNotBeCalled();
        $eventDispatcherProphecy->dispatch(Argument::any())->shouldNotBeCalled();

        $listener = new CommentListener($serviceLocatorProphecy->reveal());
        $listener->onCommentPost(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $comment));
    }
}
