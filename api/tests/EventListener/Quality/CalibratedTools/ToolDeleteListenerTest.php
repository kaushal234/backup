<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Quality\CalibratedTools;

use App\Entity\Quality\CalibratedTools\Tool;
use App\EventListener\Quality\CalibratedTools\ToolDeleteListener;
use App\Workflow\WorkflowStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ToolDeleteListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testDeleteAToolRemoveItsSerialNumber()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $toolProphecy = $this->prophesize(Tool::class);
        $requestProphecy = $this->prophesize(Request::class);

        $requestProphecy->isMethod(Request::METHOD_DELETE)->shouldBeCalledTimes(1)->willReturn(true);

        $serviceLocatorProphecy->get(WorkflowStatusUpdater::class)->shouldBeCalledTimes(1)->willReturn($workflowStatusUpdaterProphecy->reveal());
        $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldBeCalledTimes(1)->willReturn($entityManagerProphecy->reveal());
        $workflowStatusUpdaterProphecy->applyStatus($toolProphecy, Tool::SCRAPPED)->shouldBeCalledTimes(1);
        $toolProphecy->setSerialNumber(null)->shouldBeCalledTimes(1);
        $entityManagerProphecy->persist($toolProphecy)->shouldBeCalledTimes(1);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        $listener = new ToolDeleteListener($serviceLocatorProphecy->reveal());
        $listener->onDelete(new ViewEvent(static::$kernel, $requestProphecy->reveal(), HttpKernelInterface::MAIN_REQUEST, $toolProphecy->reveal()));
    }
}
