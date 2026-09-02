<?php

declare(strict_types=1);

namespace App\Tests\Workflow\Handler\Service;

use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\Workflow\Handler\Service\CustomerServiceRecord\PlannedToPendingHandler;
use App\Workflow\WorkflowStatusUpdater;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class PlannedToPendingHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testSupported()
    {
        $current = new CustomerServiceRecord();
        $current->plannedAt = null;
        $current->leader = null;

        $previous = new CustomerServiceRecord();
        $previous->setStatus(CustomerServiceRecord::PLANNED);

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $assignedHandler = new PlannedToPendingHandler($workflowStatusUpdaterProphecy->reveal());

        $support = $assignedHandler->support($current, $previous);
        self::assertTrue($support);
    }

    /** @dataProvider notSupportedProvider */
    public function testNotSupported(object $current, ?object $previous)
    {
        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $assignedHandler = new PlannedToPendingHandler($workflowStatusUpdaterProphecy->reveal());

        $support = $assignedHandler->support($current, $previous);
        self::assertFalse($support);
    }

    public function notSupportedProvider()
    {
        yield 'No previous data' => [new \stdClass(), null];

        $current = new CustomerServiceRecord();
        $current->plannedAt = null;
        $current->leader = null;

        $previous = new CustomerServiceRecord();
        $previous->setStatus(CustomerServiceRecord::PLANNED);

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $assignedHandler = new PlannedToPendingHandler($workflowStatusUpdaterProphecy->reveal());

        $support = $assignedHandler->support($current, $previous);
        self::assertTrue($support);

        yield 'Class not supported' => [new \stdClass(), $previous];

        $previous->setStatus(CustomerServiceRecord::ASSIGNED);
        yield 'Status not supported' => [$current, $previous];

        $previous->setStatus(CustomerServiceRecord::PLANNED);
        $current->plannedAt = new \DateTime();
        yield 'No planned date' => [$current, $previous];

        $current->plannedAt = null;
        $current->leader = new People();
        yield 'No leader' => [$current, $previous];
    }

    public function testHandle()
    {
        $current = new CustomerServiceRecord();
        $previous = new CustomerServiceRecord();

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $workflowStatusUpdaterProphecy->applyStatus($current, CustomerServiceRecord::PENDING)->shouldBeCalledOnce();

        $assignedHandler = new PlannedToPendingHandler($workflowStatusUpdaterProphecy->reveal());

        $assignedHandler->handle($current, $previous);
    }
}
