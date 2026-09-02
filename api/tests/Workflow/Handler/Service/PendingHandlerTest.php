<?php

declare(strict_types=1);

namespace App\Tests\Workflow\Handler\Service;

use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Factory\Service\InterventionFactory;
use App\Workflow\Handler\Service\CustomerServiceRecord\PendingHandler;
use App\Workflow\WorkflowStatusUpdater;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class PendingHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @dataProvider notSupportedProvider */
    public function testNotSupported(object $current, ?object $previous)
    {
        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $interventionFactoryProphecy = $this->prophesize(InterventionFactory::class);

        $pendingHandler = new PendingHandler($workflowStatusUpdaterProphecy->reveal(), $interventionFactoryProphecy->reveal());

        $support = $pendingHandler->support($current, $previous);
        self::assertFalse($support);
    }

    public function notSupportedProvider()
    {
        yield 'No previous data' => [new CustomerServiceRecord(), null];

        $previous = new CustomerServiceRecord();
        yield 'Class not supported' => [new \stdClass(), $previous];

        $previous->setStatus('ASSIGNED');
        yield 'Status not supported' => [new CustomerServiceRecord(), $previous];

        $current = new CustomerServiceRecord();
        $current->plannedAt = null;
        yield 'Planned at is null' => [$current, new CustomerServiceRecord()];
    }

    public function testSupported()
    {
        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $interventionFactoryProphecy = $this->prophesize(InterventionFactory::class);

        $pendingHandler = new PendingHandler($workflowStatusUpdaterProphecy->reveal(), $interventionFactoryProphecy->reveal());

        $current = new CustomerServiceRecord();
        $current->plannedAt = new \DateTime();

        $previous = new CustomerServiceRecord();
        $previous->setStatus(CustomerServiceRecord::PENDING);

        $support = $pendingHandler->support($current, $previous);
        self::assertTrue($support);
    }

    public function testChangeToPlanned()
    {
        $current = new CustomerServiceRecord();
        $previous = new CustomerServiceRecord();

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $workflowStatusUpdaterProphecy->applyStatus($current, CustomerServiceRecord::PLANNED)->shouldBeCalledOnce();

        $interventionFactoryProphecy = $this->prophesize(InterventionFactory::class);

        $pendingHandler = new PendingHandler($workflowStatusUpdaterProphecy->reveal(), $interventionFactoryProphecy->reveal());

        $pendingHandler->handle($current, $previous);
    }

    public function testChangeToAssigned()
    {
        $current = new CustomerServiceRecord();
        $current->leader = new People();

        $previous = new CustomerServiceRecord();

        $interventionFactoryProphecy = $this->prophesize(InterventionFactory::class);
        $interventionFactoryProphecy->createFromCustomerServiceRecord($current)->shouldBeCalledOnce()->willReturn(new Intervention());

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $workflowStatusUpdaterProphecy->applyStatus($current, CustomerServiceRecord::ASSIGNED)->shouldBeCalledOnce();

        $pendingHandler = new PendingHandler($workflowStatusUpdaterProphecy->reveal(), $interventionFactoryProphecy->reveal());

        $pendingHandler->handle($current, $previous);
    }
}
