<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Service;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\EventListener\Service\CustomerServiceRecordAssignmentListener;
use App\Workflow\Handler\ChainWorkflowHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CustomerServiceRecordAssignmentListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testSupportWithOtherClassThanCustomerServiceRecord(): void
    {
        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->getMethod()->shouldNotBeCalled();

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(ChainWorkflowHandler::class)->shouldNotBeCalled();
        $customerServiceRecordAssignmentListener = new CustomerServiceRecordAssignmentListener($serviceLocatorProphecy->reveal());

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $requestProphecy->reveal(),
            1,
            new \stdClass()
        );

        $customerServiceRecordAssignmentListener->assignment($event);
    }

    /** @dataProvider requestMethodProvider */
    public function testOnlyPutMethodAllowed($requestMethod, $expected)
    {
        $bagProphecy = $this->prophesize(ParameterBag::class);
        $bagProphecy->get('previous_data')->willReturn(null);

        $request = new Request();
        $request->attributes = $bagProphecy->reveal();
        $request->setMethod($requestMethod);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->isGranted(Argument::any())->willReturn(true);

        $chainWorkflowProphecy = $this->prophesize(ChainWorkflowHandler::class);
        $chainWorkflowProphecy->handle(Argument::any())->shouldNotBeCalled();
        $serviceLocatorProphecy->get(ChainWorkflowHandler::class)->willReturn($chainWorkflowProphecy->reveal());

        if ($expected) {
            $serviceLocatorProphecy->get(Security::class)->shouldBeCalled()->willReturn($securityProphecy->reveal());
            $chainWorkflowProphecy->handle(Argument::cetera())->shouldBeCalled();
        } else {
            $serviceLocatorProphecy->get(Security::class)->shouldNotBeCalled();
            $chainWorkflowProphecy->handle(Argument::cetera())->shouldNotBeCalled();
        }

        $customerServiceRecordAssignmentListener = new CustomerServiceRecordAssignmentListener($serviceLocatorProphecy->reveal());

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            1,
            new CustomerServiceRecord()
        );

        $customerServiceRecordAssignmentListener->assignment($event);
    }

    public function requestMethodProvider()
    {
        yield Request::METHOD_GET => [Request::METHOD_GET, false];
        yield Request::METHOD_PUT => [Request::METHOD_PUT, true];
        yield Request::METHOD_POST => [Request::METHOD_POST, true];
        yield Request::METHOD_HEAD => [Request::METHOD_HEAD, false];
        yield Request::METHOD_PATCH => [Request::METHOD_PATCH, false];
        yield Request::METHOD_DELETE => [Request::METHOD_DELETE, false];
        yield Request::METHOD_PURGE => [Request::METHOD_PURGE, false];
        yield Request::METHOD_OPTIONS => [Request::METHOD_OPTIONS, false];
        yield Request::METHOD_TRACE => [Request::METHOD_TRACE, false];
        yield Request::METHOD_CONNECT => [Request::METHOD_CONNECT, false];
    }

    public function testWorkflowHandlersHasBeenCalled()
    {
        $chainWorkflowHandlerProphecy = $this->prophesize(ChainWorkflowHandler::class);
        $chainWorkflowHandlerProphecy
            ->handle(Argument::type(CustomerServiceRecord::class), Argument::type(CustomerServiceRecord::class))
            ->shouldBeCalledOnce()
        ;

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy
            ->isGranted('FEATURE_CUSTOMER_SERVICE_RECORD_PLANNER')
            ->willReturn(true)
            ->shouldBeCalledOnce()
        ;

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy
            ->get(ChainWorkflowHandler::class)
            ->shouldBeCalledOnce()
            ->willReturn($chainWorkflowHandlerProphecy->reveal())
        ;
        $serviceLocatorProphecy
            ->get(Security::class)
            ->shouldBeCalledOnce()
            ->willReturn($securityProphecy->reveal())
        ;

        $customerServiceRecordAssignmentListener = new CustomerServiceRecordAssignmentListener($serviceLocatorProphecy->reveal());

        $bagProphecy = $this->prophesize(ParameterBag::class);
        $bagProphecy->get('previous_data')->willReturn(new CustomerServiceRecord())->shouldBeCalledOnce();
        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);
        $request->attributes = $bagProphecy->reveal();

        $customerServiceRecord = new CustomerServiceRecord();
        $customerServiceRecord->plannedAt = new \DateTime();

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            1,
            $customerServiceRecord
        );

        $customerServiceRecordAssignmentListener->assignment($event);
    }

    public function testAssignmentNotCalledIfUserNotGranted()
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access Denied.');

        $chainWorkflowHandlerProphecy = $this->prophesize(ChainWorkflowHandler::class);
        $chainWorkflowHandlerProphecy
            ->handle(Argument::type(CustomerServiceRecord::class), Argument::type(CustomerServiceRecord::class))
            ->shouldNotBeCalled()
        ;

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy
            ->isGranted('FEATURE_CUSTOMER_SERVICE_RECORD_PLANNER')
            ->willReturn(false)
            ->shouldBeCalledOnce()
        ;

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy
            ->get(ChainWorkflowHandler::class)
            ->shouldNotBeCalled()
        ;
        $serviceLocatorProphecy
            ->get(Security::class)
            ->shouldBeCalledOnce()
            ->willReturn($securityProphecy->reveal())
        ;

        $customerServiceRecordAssignmentListener = new CustomerServiceRecordAssignmentListener($serviceLocatorProphecy->reveal());

        $bagProphecy = $this->prophesize(ParameterBag::class);
        $bagProphecy->get('previous_data')->willReturn(new CustomerServiceRecord())->shouldBeCalledOnce();
        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);
        $request->attributes = $bagProphecy->reveal();

        $customerServiceRecord = new CustomerServiceRecord();
        $customerServiceRecord->plannedAt = new \DateTime();

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            1,
            $customerServiceRecord
        );

        $customerServiceRecordAssignmentListener->assignment($event);
    }

    public function testSubscribeEvent()
    {
        $expected = [
            KernelEvents::VIEW => [
                ['assignment', EventPriorities::PRE_VALIDATE],
            ],
        ];

        self::assertSame($expected, CustomerServiceRecordAssignmentListener::getSubscribedEvents());
    }

    public function testSubscribedServices()
    {
        $expected = [
            ChainWorkflowHandler::class,
            Security::class,
        ];

        self::assertSame($expected, CustomerServiceRecordAssignmentListener::getSubscribedServices());
    }
}
