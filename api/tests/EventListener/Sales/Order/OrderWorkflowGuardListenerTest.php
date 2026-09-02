<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Sales\Order;

use App\Entity\Sales\Order;
use App\EventListener\Sales\Order\OrderWorkflowGuardListener;
use LegacyBundle\Manager\SalesOrderLineManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;

class OrderWorkflowGuardListenerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider wrongObjectDataProvider
     */
    public function testEventListenerOnlyDoesntAffetsWrongObject($object)
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(SalesOrderLineManager::class)->shouldNotBeCalled();
        $listener = new OrderWorkflowGuardListener($serviceLocatorProphecy->reveal());

        $event = new GuardEvent($object, new Marking(), new Transition('foo', [], []));
        $listener->guardInProgress($event);
        $listener->guardClosed($event);

        self::assertFalse($event->isBlocked());
    }

    public function wrongObjectDataProvider(): \Generator
    {
        yield 'with stdClass' => [new \stdClass()];
        yield 'order without legacyId' => [new Order()];
    }

    public function testGuardInProgressWithNoSOLs()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $order = (new Order())->setLegacyId(42);

        $salesOrderLineManagerProphecy = $this->prophesize(SalesOrderLineManager::class);
        $serviceLocatorProphecy->get(SalesOrderLineManager::class)->shouldBeCalledTimes(1)->willReturn($salesOrderLineManagerProphecy->reveal());
        $salesOrderLineManagerProphecy->getSalesOrderLines($order)->willReturn([]);
        $listener = new OrderWorkflowGuardListener($serviceLocatorProphecy->reveal());

        $event = new GuardEvent($order, new Marking(), new Transition('foo', [], []));
        $listener->guardInProgress($event);
        self::assertTrue($event->isBlocked());
    }

    public function testGuardInProgressWithSolNotReachingFactory()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $order = (new Order())->setLegacyId(42);

        $salesOrderLineManagerProphecy = $this->prophesize(SalesOrderLineManager::class);
        $sols = [
            ['id' => 1, 'dt_create_factory' => '2019-04-29 10:00:00'],
            ['id' => 42],
            ['id' => 51, 'dt_create_factory' => '2019-04-29 00:00:00'],
            ['id' => 1_984, 'dt_create_factory' => '0000-00-00 00:00:00'],
        ];
        $serviceLocatorProphecy->get(SalesOrderLineManager::class)->shouldBeCalledTimes(1)->willReturn($salesOrderLineManagerProphecy->reveal());
        $salesOrderLineManagerProphecy->getSalesOrderLines($order)->willReturn($sols);
        $listener = new OrderWorkflowGuardListener($serviceLocatorProphecy->reveal());

        $event = new GuardEvent($order, new Marking(), new Transition('foo', [], []));

        $listener->guardInProgress($event);
        self::assertTrue($event->isBlocked());
    }

    public function testGuardInProgressOk()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $order = (new Order())->setLegacyId(42);

        $salesOrderLineManagerProphecy = $this->prophesize(SalesOrderLineManager::class);
        $sols = [
            ['id' => 1, 'dt_create_factory' => '2019-04-21 10:00:00'],
            ['id' => 42, 'dt_create_factory' => '2019-04-22 10:00:00'],
            ['id' => 51, 'dt_create_factory' => '2019-04-23 10:00:00'],
            ['id' => 1_984, 'dt_create_factory' => '2019-04-24 10:00:00'],
        ];
        $serviceLocatorProphecy->get(SalesOrderLineManager::class)->shouldBeCalledTimes(1)->willReturn($salesOrderLineManagerProphecy->reveal());
        $salesOrderLineManagerProphecy->getSalesOrderLines($order)->willReturn($sols);
        $listener = new OrderWorkflowGuardListener($serviceLocatorProphecy->reveal());

        $event = new GuardEvent($order, new Marking(), new Transition('foo', [], []));
        $listener->guardInProgress($event);
        self::assertFalse($event->isBlocked());
    }

    public function testGuardCloseWithUnclosedSOLs()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $order = (new Order())->setLegacyId(42);

        $salesOrderLineManagerProphecy = $this->prophesize(SalesOrderLineManager::class);
        $serviceLocatorProphecy->get(SalesOrderLineManager::class)->shouldBeCalledTimes(1)->willReturn($salesOrderLineManagerProphecy->reveal());
        $salesOrderLineManagerProphecy->isPreventingOrderClosing($order)->willReturn(true);
        $listener = new OrderWorkflowGuardListener($serviceLocatorProphecy->reveal());

        $event = new GuardEvent($order, new Marking(), new Transition('foo', [], []));
        $listener->guardClosed($event);
        self::assertTrue($event->isBlocked());
    }

    public function testGuardClosedOk()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $order = (new Order())->setLegacyId(42);

        $salesOrderLineManagerProphecy = $this->prophesize(SalesOrderLineManager::class);
        $serviceLocatorProphecy->get(SalesOrderLineManager::class)->shouldBeCalledTimes(1)->willReturn($salesOrderLineManagerProphecy->reveal());
        $salesOrderLineManagerProphecy->isPreventingOrderClosing($order)->willReturn(false);
        $listener = new OrderWorkflowGuardListener($serviceLocatorProphecy->reveal());

        $event = new GuardEvent($order, new Marking(), new Transition('foo', [], []));
        $listener->guardClosed($event);
        self::assertFalse($event->isBlocked());
    }
}
