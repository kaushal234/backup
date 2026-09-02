<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Common;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Entity\User;
use App\Event\Activity\SubscriptionCreatedEvent;
use App\EventListener\Common\SubscriptionListener;
use App\Request\Activity\CommentRequestManager;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SubscriptionListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @dataProvider provideTestData
     */
    public function testCreationOrDeletionOfSubscriptionAddsAComment(string $method, ?User $user, Subscription $subscription, string $message)
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $request = new Request();
        $request->setMethod($method);

        $securityProphecy = $this->prophesize(Security::class);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($user);

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $serviceLocatorProphecy->get(IriConverterInterface::class)->shouldBeCalledTimes(1)->willReturn($iriConverterProphecy->reveal());
        $item = new \stdClass();
        $iriConverterProphecy->getResourceFromIri($subscription->getResource())->shouldBeCalledTimes(1)->willReturn($item);

        $commentRequestManagerProphecy = $this->prophesize(CommentRequestManager::class);
        $serviceLocatorProphecy->get(CommentRequestManager::class)->shouldBeCalledTimes(1)->willReturn($commentRequestManagerProphecy->reveal());
        $commentRequestManagerProphecy->insertComment($item, $message, null, ['subscription' => null])->shouldBeCalledTimes(1);

        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        if ('POST' === $method) {
            $eventDispatcherProphecy->dispatch(Argument::that(static fn (SubscriptionCreatedEvent $event) => $event->getItem() === $item));
            $serviceLocatorProphecy->get(EventDispatcherInterface::class)->shouldBeCalledTimes(1)->willReturn($eventDispatcherProphecy->reveal());
        }

        $listener = new SubscriptionListener($serviceLocatorProphecy->reveal());

        $listener->afterSubscriptionCreationOrDeletion(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $subscription));
    }

    public function provideTestData()
    {
        $subscriber = (new People())->setFirstname('Lower')->setLastname('Faux');

        yield 'Subscription creation with no user connected' => [
            'POST',
            null,
            (new Subscription())->setResource('/resources/42')->setUser($subscriber),
            'FAUX, Lower has been added as subscriber',
        ];

        yield 'Subscription creation with user connected being the same than the subscription ' => [
            'POST',
            $subscriber,
            (new Subscription())->setResource('/resources/42')->setUser($subscriber),
            'FAUX, Lower subscribed',
        ];

        yield 'Subscription deletion with no user connected' => [
            'DELETE',
            null,
            (new Subscription())->setResource('/resources/42')->setUser($subscriber),
            'FAUX, Lower has been removed as subscriber',
        ];

        yield 'Subscription deletion with user connected being the same than the subscription ' => [
            'DELETE',
            $subscriber,
            (new Subscription())->setResource('/resources/42')->setUser($subscriber),
            'FAUX, Lower unsubscribed',
        ];
    }

    public function testThatAnotherHTTPMethodThanPostOrDeleteDoesNotTriggerAnything()
    {
        $subscription = (new Subscription())->setResource('/resources/42');

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $request = new Request();
        $request->setMethod('PATCH');

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $serviceLocatorProphecy->get(IriConverterInterface::class)->shouldBeCalledTimes(1)->willReturn($iriConverterProphecy->reveal());
        $item = new \stdClass();
        $iriConverterProphecy->getResourceFromIri($subscription->getResource())->shouldBeCalledTimes(1)->willReturn($item);

        $serviceLocatorProphecy->get(Security::class)->shouldNotBeCalled();
        $serviceLocatorProphecy->get(CommentRequestManager::class)->shouldNotBeCalled();

        $listener = new SubscriptionListener($serviceLocatorProphecy->reveal());

        $listener->afterSubscriptionCreationOrDeletion(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, (new Subscription())->setResource('/resources/42')));
    }

    public function testListenerOnlyAppliesToSubscriptionClass()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $request = new Request();
        $request->setMethod('POST');

        $serviceLocatorProphecy->get(IriConverterInterface::class)->shouldNotBeCalled();
        $serviceLocatorProphecy->get(Security::class)->shouldNotBeCalled();
        $serviceLocatorProphecy->get(CommentRequestManager::class)->shouldNotBeCalled();
        $serviceLocatorProphecy->get(EventDispatcherInterface::class)->shouldNotBeCalled();

        $listener = new SubscriptionListener($serviceLocatorProphecy->reveal());

        $listener->afterSubscriptionCreationOrDeletion(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, null));
    }
}
