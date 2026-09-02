<?php

declare(strict_types=1);

namespace App\Tests\Workflow\Handler;

use App\Workflow\Handler\ChainWorkflowHandler;
use App\Workflow\Handler\WorkflowHandlerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class ChainWorkflowHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testHandle()
    {
        $handlerNotSupportProphecy = $this->prophesize(WorkflowHandlerInterface::class);
        $handlerNotSupportProphecy->support(Argument::cetera())->shouldBeCalledOnce()->willReturn(false);
        $handlerNotSupportProphecy->handle(Argument::cetera())->shouldNotBeCalled();

        $handlerWithSupportProphecy = $this->prophesize(WorkflowHandlerInterface::class);
        $handlerWithSupportProphecy->support(Argument::cetera())->shouldBeCalledOnce()->willReturn(true);
        $handlerWithSupportProphecy->handle(Argument::type('object'), Argument::type('object'))->shouldBeCalledOnce();

        $handler = new ChainWorkflowHandler([$handlerNotSupportProphecy->reveal(), $handlerWithSupportProphecy->reveal()]);
        $handler->handle(new \stdClass(), new \stdClass());
    }
}
