<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Workflow;

use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\Entity\UpdatableStatusEntityInterface;
use App\EventListener\Workflow\UpdatableStatusEntityListener;
use App\Workflow\WorkflowStatusUpdater;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class UpdatableStatusEntityListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testSupportClassNotImplementUpdatableStatusEntityInterface(): void
    {
        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->getMethod()->shouldNotBeCalled();

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(WorkflowStatusUpdater::class)->shouldNotBeCalled();
        $updatableStatusEntityListener = new UpdatableStatusEntityListener($serviceLocatorProphecy->reveal());

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $requestProphecy->reveal(),
            1,
            new \stdClass()
        );

        $updatableStatusEntityListener->changeStatus($event);
    }

    public function testOnlyPutMethodAllowed(): void
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->attributes->set('_route', 'foo');

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(WorkflowStatusUpdater::class)->shouldNotBeCalled();
        $updatableStatusEntityListener = new UpdatableStatusEntityListener($serviceLocatorProphecy->reveal());

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            1,
            new CustomerServiceRecord()
        );

        $updatableStatusEntityListener->changeStatus($event);
    }

    public function testWorkflowStatusUpdaterHasBeenCalled()
    {
        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $workflowStatusUpdaterProphecy
            ->applyStatus(Argument::type(UpdatableStatusEntityInterface::class), 'CHANGED')
            ->shouldBeCalledOnce()
        ;

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy
            ->get(WorkflowStatusUpdater::class)
            ->shouldBeCalledOnce()
            ->willReturn($workflowStatusUpdaterProphecy->reveal())
        ;
        $updatableStatusEntityListener = new UpdatableStatusEntityListener($serviceLocatorProphecy->reveal());

        $bagProphecy = $this->prophesize(ParameterBag::class);
        $bagProphecy->get('_faq_status_override')->willReturn(null);
        $bagProphecy->get('previous_data')->willReturn(new ClassUpdatableStatusEntityInterface())->shouldBeCalledOnce();
        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);
        $request->attributes = $bagProphecy->reveal();

        $newInstance = new ClassUpdatableStatusEntityInterface();
        $newInstance->status = 'CHANGED';

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            1,
            $newInstance
        );

        $updatableStatusEntityListener->changeStatus($event);
    }
}

class ClassUpdatableStatusEntityInterface implements UpdatableStatusEntityInterface
{
    public string $status = 'INITIAL';

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): UpdatableStatusEntityInterface
    {
        $this->status = $status;

        return $this;
    }
}
