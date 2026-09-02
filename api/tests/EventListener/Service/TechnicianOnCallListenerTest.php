<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Service;

use App\Entity\Service\TechnicianOnCall;
use App\EventListener\Service\TechnicianOnCall\TechnicianOnCallListener;
use App\Workflow\Handler\ChainWorkflowHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class TechnicianOnCallListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testOnEditionApplyInProgressStatusSkipsWhenRouteDoesNotMatch(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(ChainWorkflowHandler::class)->shouldNotBeCalled();

        $request = new Request();
        $request->attributes->set('_route', 'some_other_route');

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            0,
            new TechnicianOnCall()
        );

        (new TechnicianOnCallListener($container->reveal()))->onEditionApplyInProgressStatus($event);
    }

    public function testOnEditionApplyInProgressStatusCallsWorkflowHandlerOnCorrectRoute(): void
    {
        $technicianOnCall = new TechnicianOnCall();

        $workflowHandler = $this->prophesize(ChainWorkflowHandler::class);
        $workflowHandler->handle($technicianOnCall, null)->shouldBeCalledOnce();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(ChainWorkflowHandler::class)->willReturn($workflowHandler->reveal());

        $request = new Request();
        $request->attributes->set('_route', 'technician_on_call_edit');

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            0,
            $technicianOnCall
        );

        (new TechnicianOnCallListener($container->reveal()))->onEditionApplyInProgressStatus($event);
    }

    public function testOnTechnicianRequestedApplyPendingStatusSkipsWhenRouteDoesNotMatch(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(ChainWorkflowHandler::class)->shouldNotBeCalled();

        $request = new Request();
        $request->attributes->set('_route', 'some_other_route');

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            0,
            new TechnicianOnCall()
        );

        (new TechnicianOnCallListener($container->reveal()))->onTechnicianRequestedApplyPendingStatus($event);
    }

    public function testOnTechnicianRequestedApplyPendingStatusCallsWorkflowHandlerOnCorrectRoute(): void
    {
        $technicianOnCall = new TechnicianOnCall();

        $workflowHandler = $this->prophesize(ChainWorkflowHandler::class);
        $workflowHandler->handle($technicianOnCall, null)->shouldBeCalledOnce();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(ChainWorkflowHandler::class)->willReturn($workflowHandler->reveal());

        $request = new Request();
        $request->attributes->set('_route', 'technician_on_call_request_technician');

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            0,
            $technicianOnCall
        );

        (new TechnicianOnCallListener($container->reveal()))->onTechnicianRequestedApplyPendingStatus($event);
    }

    public function testWhenFactoryPaysCreateWCSkipsWhenMethodIsNotPostOrPut(): void
    {
        $this->expectNotToPerformAssertions();
        $container = $this->prophesize(ContainerInterface::class);

        $request = new Request();

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            0,
            new TechnicianOnCall()
        );

        (new TechnicianOnCallListener($container->reveal()))->whenFactoryPaysCreateWC($event);
    }
}
