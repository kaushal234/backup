<?php

declare(strict_types=1);

namespace App\Tests\Workflow\Handler\Service;

use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Factory\Service\InterventionFactory;
use App\Workflow\Handler\Service\CustomerServiceRecord\PlannedToAssignedHandler;
use App\Workflow\WorkflowStatusUpdater;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class PlannedToAssignedHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testSupported()
    {
        $current = new CustomerServiceRecord();
        $current->plannedAt = new \DateTime();
        $current->leader = new People();

        $previous = new CustomerServiceRecord();
        $previous->setStatus(CustomerServiceRecord::PLANNED);

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $interventionFactoryProphecy = $this->prophesize(InterventionFactory::class);
        $assignedHandler = new PlannedToAssignedHandler($workflowStatusUpdaterProphecy->reveal(), $interventionFactoryProphecy->reveal());

        $support = $assignedHandler->support($current, $previous);
        self::assertTrue($support);
    }

    /** @dataProvider notSupportedProvider */
    public function testNotSupported(object $current, ?object $previous)
    {
        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $interventionFactoryProphecy = $this->prophesize(InterventionFactory::class);
        $assignedHandler = new PlannedToAssignedHandler($workflowStatusUpdaterProphecy->reveal(), $interventionFactoryProphecy->reveal());

        $support = $assignedHandler->support($current, $previous);
        self::assertFalse($support);
    }

    public function notSupportedProvider()
    {
        yield 'No previous data' => [new \stdClass(), null];

        $current = new CustomerServiceRecord();
        $current->plannedAt = new \DateTime();
        $current->leader = new People();

        $previous = new CustomerServiceRecord();
        $previous->setStatus(CustomerServiceRecord::PLANNED);

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $interventionFactoryProphecy = $this->prophesize(InterventionFactory::class);
        $assignedHandler = new PlannedToAssignedHandler($workflowStatusUpdaterProphecy->reveal(), $interventionFactoryProphecy->reveal());

        $support = $assignedHandler->support($current, $previous);
        self::assertTrue($support);

        yield 'Class not supported' => [new \stdClass(), $previous];

        $previous->setStatus(CustomerServiceRecord::ASSIGNED);
        yield 'Status not supported' => [$current, $previous];

        $previous->setStatus(CustomerServiceRecord::PLANNED);
        $current->plannedAt = null;
        yield 'No planned date' => [$current, $previous];

        $current->plannedAt = new \DateTime();
        $current->leader = null;
        yield 'No leader' => [$current, $previous];
    }

    public function testHandle()
    {
        $current = new CustomerServiceRecord();
        $previous = new CustomerServiceRecord();

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $workflowStatusUpdaterProphecy->applyStatus($current, CustomerServiceRecord::ASSIGNED)->shouldBeCalledOnce();

        $intervention = new Intervention();

        $interventionFactoryProphecy = $this->prophesize(InterventionFactory::class);
        $interventionFactoryProphecy->createFromCustomerServiceRecord($current)->shouldBeCalledOnce()->willreturn($intervention);

        $assignedHandler = new PlannedToAssignedHandler($workflowStatusUpdaterProphecy->reveal(), $interventionFactoryProphecy->reveal());

        $assignedHandler->handle($current, $previous);

        self::assertSame($intervention, $current->getOpenIntervention());
    }
}
