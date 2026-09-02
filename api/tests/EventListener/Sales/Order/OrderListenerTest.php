<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Sales\Order;

use ApiPlatform\Symfony\Routing\Router;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerRelationshipTeam;
use App\Entity\Sales\MainSalesRepresentative;
use App\Entity\Sales\Order;
use App\EventListener\Sales\Order\OrderListener;
use App\Notifier\Tasks\LegacyTaskNotifier;
use Doctrine\Common\Collections\ArrayCollection;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;

class OrderListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testAutofillBuyer()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $customer = (new Customer())->setName('Set may name, set my name!');
        $order = (new Order())->setEndUser($customer);

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $listener = new OrderListener($serviceLocatorProphecy->reveal());
        $listener->autofillBuyer(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $order));

        self::assertSame($customer, $order->getEndUser());
        self::assertSame('Set may name, set my name!', $order->getCustomerName());
        self::assertSame($customer, $order->getBuyer());
    }

    /** @dataProvider badObjectsAndMethodsProvider */
    public function testCRTNotificationCaresAboutOrderAndPostMethod($object, ?string $method)
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(Argument::any())->shouldNotBeCalled();

        $request = new Request();
        if (null !== $method) {
            $request->setMethod($method);
        }

        $listener = new OrderListener($serviceLocatorProphecy->reveal());
        $listener->checkCRT(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $object));
    }

    public function badObjectsAndMethodsProvider()
    {
        yield \stdClass::class => [new \stdClass(), null];
        yield 'Order on GET' => [new Order(), 'Get'];
        yield 'Order on PUT' => [new Order(), 'Get'];
        yield 'Order on DELETE' => [new Order(), 'Get'];
    }

    public function testCRTNotificationAreNoTrigerredWithoutAsms()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $endUserProphecy = $this->prophesize(Customer::class);
        $endUserProphecy->getMainSalesRepresentative()->willReturn(null);
        $buyerProphecy = $this->prophesize(Customer::class);
        $buyerProphecy->getMainSalesRepresentative()->willReturn(null);
        $locationProphecy = $this->prophesize(Location::class);

        $orderProphecy = $this->prophesize(Order::class);
        $orderProphecy->getSso()->willReturn($locationProphecy->reveal());
        $orderProphecy->getEndUser()->willReturn($endUserProphecy->reveal());
        $orderProphecy->getBuyer()->willReturn($buyerProphecy->reveal());

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $taskManagerProphecy = $this->prophesize(TaskManager::class);
        $serviceLocatorProphecy->get(TaskManager::class)->shouldNotBeCalled();
        $taskManagerProphecy->insert(Argument::any())->shouldNotBeCalled();
        $notifierProphecy = $this->prophesize(LegacyTaskNotifier::class);
        $serviceLocatorProphecy->get(LegacyTaskNotifier::class)->shouldNotBeCalled();
        $notifierProphecy->sendEmail(Argument::any())->shouldNotBeCalled();

        $listener = new OrderListener($serviceLocatorProphecy->reveal());
        $listener->checkCRT(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $orderProphecy->reveal()));
    }

    public function testCRTNotificationAreNoTrigerredWithCrt()
    {
        $mainRepresentative = new MainSalesRepresentative();
        $mainRepresentative->asm = new People();
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(Argument::any())->shouldNotBeCalled();
        $endUserProphecy = $this->prophesize(Customer::class);
        $endUserProphecy->getMainSalesRepresentative()->willReturn($mainRepresentative);
        $endUserProphecy->getCrt()->willReturn(new ArrayCollection([new CustomerRelationshipTeam()]));
        $buyerProphecy = $this->prophesize(Customer::class);
        $buyerProphecy->getMainSalesRepresentative()->willReturn($mainRepresentative);
        $buyerProphecy->getCrt()->willReturn(new ArrayCollection([new CustomerRelationshipTeam()]));
        $locationProphecy = $this->prophesize(Location::class);

        $orderProphecy = $this->prophesize(Order::class);
        $orderProphecy->getSso()->willReturn($locationProphecy->reveal());
        $orderProphecy->getEndUser()->willReturn($endUserProphecy->reveal());
        $orderProphecy->getBuyer()->willReturn($buyerProphecy->reveal());

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $listener = new OrderListener($serviceLocatorProphecy->reveal());
        $listener->checkCRT(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $orderProphecy->reveal()));
    }

    public function testCRTNotification()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $supervisor = new People();
        $supervisor->setLastname('SUPER')->setFirstname('visor')->setEmail('supevisor@tld.fr');
        $asmEndUser = new People();
        $asmEndUser->setSupervisor($supervisor)->setLastname('ASM')->setFirstname('end user')->setEmail('asm-enduser@tld.fr');
        $asmBuyer = new People();
        $asmBuyer->setLastname('ASM')->setFirstname('buyer')->setEmail('asm-buyer@tld.fr');
        $mainRepresentativeEndUser = new MainSalesRepresentative();
        $mainRepresentativeEndUser->asm = $asmEndUser;
        $mainRepresentativeBuyer = new MainSalesRepresentative();
        $mainRepresentativeBuyer->asm = $asmBuyer;
        $endUserProphecy = $this->prophesize(Customer::class);

        $endUserProphecy->getId()->willReturn(42);
        $endUserProphecy->getName()->willReturn('fourty two');
        $endUserProphecy->getMainSalesRepresentative()->willReturn($mainRepresentativeEndUser);
        $endUserProphecy->getCrt()->willReturn(new ArrayCollection());
        $endUserProphecy->getLegacyId()->willReturn(12);
        $buyerProphecy = $this->prophesize(Customer::class);

        $buyerProphecy->getId()->willReturn(1_984);
        $buyerProphecy->getName()->willReturn('nineteen eighty four');
        $buyerProphecy->getMainSalesRepresentative()->willReturn($mainRepresentativeBuyer);
        $buyerProphecy->getCrt()->willReturn(new ArrayCollection());
        $buyerProphecy->getLegacyId()->willReturn(13);
        $locationProphecy = $this->prophesize(Location::class);
        $location = $locationProphecy->reveal();

        $orderProphecy = $this->prophesize(Order::class);
        $orderProphecy->getSso()->willReturn($location)->shouldBeCalledTimes(1);
        $endUser = $endUserProphecy->reveal();
        $orderProphecy->getEndUser()->willReturn($endUser)->shouldBeCalledTimes(1);
        $buyer = $buyerProphecy->reveal();
        $orderProphecy->getBuyer()->willReturn($buyer)->shouldBeCalledTimes(1);

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $taskManagerProphecy = $this->prophesize(TaskManager::class);
        $serviceLocatorProphecy->get(TaskManager::class)->shouldBeCalledTimes(2)->willReturn($taskManagerProphecy->reveal());
        $taskManagerProphecy->insert(Argument::that(static fn (Task $task) => $task->getAssignee() === $asmEndUser && $task->getAssignor() === $supervisor && 'ECUST' === $task->getModule() && $task->getLocation() === $location && 12 === $task->getParentId()))->willReturn(1);
        $taskManagerProphecy->insert(Argument::that(static fn (Task $task) => $task->getAssignee() === $asmBuyer && $task->getAssignor() === $asmBuyer && 'ECUST' === $task->getModule() && $task->getLocation() === $location && 13 === $task->getParentId()))->willReturn(1);

        $routerProphecy = $this->prophesize(RouterInterface::class);
        $serviceLocatorProphecy->get(RouterInterface::class)->shouldBeCalledTimes(2)->willReturn($routerProphecy->reveal());
        $routerProphecy->generate('customer', ['id' => 42], Router::ABSOLUTE_URL)->shouldBeCalledTimes(1);
        $routerProphecy->generate('customer', ['id' => 1_984], Router::ABSOLUTE_URL)->shouldBeCalledTimes(1);

        $notifierProphecy = $this->prophesize(LegacyTaskNotifier::class);
        $serviceLocatorProphecy->get(LegacyTaskNotifier::class)->shouldBeCalledTimes(2)->willReturn($notifierProphecy->reveal());
        $notifierProphecy->sendEmail(Argument::type(Task::class))->shouldBeCalledTimes(2);

        $listener = new OrderListener($serviceLocatorProphecy->reveal());
        $listener->checkCRT(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $orderProphecy->reveal()));
    }

    public function testCRTNotificationWithBuyerSameAsUser()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $supervisor = new People();
        $supervisor->setLastname('SUPER')->setFirstname('visor')->setEmail('supevisor@tld.fr');
        $asm = new People();
        $asm->setSupervisor($supervisor)->setLastname('ASM')->setFirstname('end user')->setEmail('asm-enduser@tld.fr');
        $mainRepresentative = new MainSalesRepresentative();
        $mainRepresentative->asm = $asm;
        $customerProphecy = $this->prophesize(Customer::class);

        $customerProphecy->getId()->willReturn(42);
        $customerProphecy->getName()->willReturn('fourty two');
        $customerProphecy->getMainSalesRepresentative()->willReturn($mainRepresentative);
        $customerProphecy->getCrt()->willReturn(new ArrayCollection());
        $customerProphecy->getLegacyId()->willReturn(13);
        $customer = $customerProphecy->reveal();
        $locationProphecy = $this->prophesize(Location::class);
        $location = $locationProphecy->reveal();

        $orderProphecy = $this->prophesize(Order::class);
        $orderProphecy->getSso()->willReturn($location)->shouldBeCalledTimes(1);
        $orderProphecy->getEndUser()->willReturn($customer)->shouldBeCalledTimes(1);
        $orderProphecy->getBuyer()->willReturn($customer)->shouldBeCalledTimes(1);

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $taskManagerProphecy = $this->prophesize(TaskManager::class);
        $serviceLocatorProphecy->get(TaskManager::class)->shouldBeCalledTimes(1)->willReturn($taskManagerProphecy->reveal());
        $taskManagerProphecy->insert(Argument::type(Task::class))->willReturn(1)->shouldBeCalledTimes(1);

        $routerProphecy = $this->prophesize(RouterInterface::class);
        $serviceLocatorProphecy->get(RouterInterface::class)->shouldBeCalledTimes(1)->willReturn($routerProphecy->reveal());
        $routerProphecy->generate('customer', ['id' => 42], Router::ABSOLUTE_URL)->shouldBeCalledTimes(1);

        $notifierProphecy = $this->prophesize(LegacyTaskNotifier::class);
        $serviceLocatorProphecy->get(LegacyTaskNotifier::class)->shouldBeCalledTimes(1)->willReturn($notifierProphecy->reveal());
        $notifierProphecy->sendEmail(Argument::type(Task::class))->shouldBeCalledTimes(1);

        $listener = new OrderListener($serviceLocatorProphecy->reveal());
        $listener->checkCRT(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $orderProphecy->reveal()));
    }
}
