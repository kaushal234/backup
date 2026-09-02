<?php

declare(strict_types=1);

namespace App\Tests\Command\Sales;

use App\Command\Sales\PendingOrdersNotificationCommand;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Sales\Order;
use App\Mailer\Sales\OrdersPoolMailer;
use App\Repository\Directory\PeopleRepository;
use App\Repository\Sales\OrderRepository;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PendingOrdersNotificationCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testExecute()
    {
        $sso1Prophecy = $this->prophesize(Location::class);
        $sso1Prophecy->getId()->shouldBeCalledTimes(3)->willReturn(42);
        $sso1 = $sso1Prophecy->reveal();

        $sso2Prophecy = $this->prophesize(Location::class);
        $sso2Prophecy->getId()->shouldBeCalledTimes(2)->willReturn(1_984);
        $sso2 = $sso2Prophecy->reveal();

        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findGroupMembers'])->getMock();
        $recipientsSso1 = [(new People())->setEmail('em@il')];
        $recipientsSso2 = [(new People())->setEmail('otherem@il')];

        $peopleRepositoryMock->expects($this->exactly(2))->method('findGroupMembers')->withConsecutive(['ROLE_SA', $sso1], ['ROLE_SA', $sso2])->willReturnOnConsecutiveCalls($recipientsSso2, $recipientsSso1);

        $order1 = (new Order())->setSso($sso2);
        $order2 = (new Order())->setSso($sso1);
        $order3 = (new Order())->setSso($sso1);
        $order4 = (new Order())->setSso($sso1);
        $order5 = (new Order())->setSso($sso2);

        $orderRepositoryMock = $this->getMockBuilder(OrderRepository::class)->disableOriginalConstructor()->onlyMethods(['findPendingOrders'])->getMock();
        $orderRepositoryMock->expects($this->once())->method('findPendingOrders')->with($this->callback(static fn ($date) => $date instanceof \DateTimeInterface))->willReturn([$order1, $order2, $order3, $order4, $order5]);

        $orderPoolMailerProphecy = $this->createMock(OrdersPoolMailer::class);
        $orderPoolMailerProphecy->expects($this->exactly(5))->method('addOrder')->withConsecutive(
            [$order1, $recipientsSso2, '1984'],
            [$order2, $recipientsSso1, '42'],
            [$order3, $recipientsSso1, '42'],
            [$order4, $recipientsSso1, '42'],
            [$order5, $recipientsSso2, '1984']
        )->willReturn($this->prophesize(OrdersPoolMailer::class)->reveal());
        $orderPoolMailerProphecy->expects($this->once())->method('send')->with('sor.pending_summary.message', 'Emails/Sales/Order/order_notify_pending_summary.html.twig');

        self::bootKernel();
        $application = new Application(self::$kernel);

        $application->addCommand(new PendingOrdersNotificationCommand(
            $peopleRepositoryMock,
            $orderRepositoryMock,
            $orderPoolMailerProphecy));

        $command = $application->find('tld:notifications:sor:orders_pending');

        (new CommandTester($command))->execute([
            'command' => 'tld:notifications:sor:orders_pending',
        ]);
    }

    public function testExecuteWithoutOrders()
    {
        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findGroupMembers'])->getMock();
        $peopleRepositoryMock->expects($this->never())->method('findGroupMembers');

        $orderRepositoryMock = $this->getMockBuilder(OrderRepository::class)->disableOriginalConstructor()->onlyMethods(['findPendingOrders'])->getMock();
        $orderRepositoryMock->expects($this->once())->method('findPendingOrders')->with($this->callback(static fn ($date) => $date instanceof \DateTimeInterface))->willReturn([]);

        $orderPoolMailerProphecy = $this->prophesize(OrdersPoolMailer::class);
        $orderPoolMailerProphecy->send(Argument::any(), Argument::any())->shouldNotBeCalled();

        self::bootKernel();
        $application = new Application(self::$kernel);

        $application->addCommand(new PendingOrdersNotificationCommand(
            $peopleRepositoryMock,
            $orderRepositoryMock,
            $orderPoolMailerProphecy->reveal()));

        $command = $application->find('tld:notifications:sor:orders_pending');

        (new CommandTester($command))->execute([
            'command' => 'tld:notifications:sor:orders_pending',
        ]);
    }
}
