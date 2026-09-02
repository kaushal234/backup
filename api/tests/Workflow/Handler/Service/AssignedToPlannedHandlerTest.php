<?php

declare(strict_types=1);

namespace App\Tests\Workflow\Handler\Service;

use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Workflow\Handler\Service\CustomerServiceRecord\AssignedToPlannedHandler;
use App\Workflow\WorkflowStatusUpdater;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AssignedToPlannedHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testSupported()
    {
        $current = new CustomerServiceRecord();
        $current->plannedAt = new \DateTime();
        $current->leader = null;

        $previous = new CustomerServiceRecord();
        $previous->setStatus(AbstractCustomerServiceRecord::ASSIGNED);

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $assignedHandler = new AssignedToPlannedHandler($workflowStatusUpdaterProphecy->reveal());

        $support = $assignedHandler->support($current, $previous);
        self::assertTrue($support);
    }

    /** @dataProvider notSupportedProvider */
    public function testNotSupported(object $current, ?object $previous)
    {
        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $assignedHandler = new AssignedToPlannedHandler($workflowStatusUpdaterProphecy->reveal());

        $support = $assignedHandler->support($current, $previous);
        self::assertFalse($support);
    }

    public function notSupportedProvider()
    {
        yield 'No previous data' => [new \stdClass(), null];

        $current = new CustomerServiceRecord();
        $current->plannedAt = new \DateTime();
        $current->leader = null;

        $previous = new CustomerServiceRecord();
        $previous->setStatus(AbstractCustomerServiceRecord::ASSIGNED);

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $assignedHandler = new AssignedToPlannedHandler($workflowStatusUpdaterProphecy->reveal());

        $support = $assignedHandler->support($current, $previous);
        // Verify first data set is supported
        self::assertTrue($support);

        yield 'Class not supported' => [new \stdClass(), $previous];

        $previous->setStatus(AbstractCustomerServiceRecord::PLANNED);
        yield 'Status not supported' => [$current, $previous];

        $previous->setStatus(AbstractCustomerServiceRecord::ASSIGNED);
        $current->leader = new People();
        yield 'No leader' => [$current, $previous];

        $current->leader = null;
        $current->plannedAt = null;
        yield 'No planned date' => [$current, $previous];
    }

    public function testHandle()
    {
        $openIntervention = new Intervention();

        $current = new CustomerServiceRecord();
        $current->addIntervention($openIntervention);

        $previous = new CustomerServiceRecord();

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $workflowStatusUpdaterProphecy->applyStatus($openIntervention, Intervention::TO_CONTINUE)->shouldBeCalledOnce();
        $workflowStatusUpdaterProphecy->applyStatus($current, AbstractCustomerServiceRecord::PLANNED)->shouldBeCalledOnce();

        $assignedHandler = new AssignedToPlannedHandler($workflowStatusUpdaterProphecy->reveal());

        $assignedHandler->handle($current, $previous);
    }
}
