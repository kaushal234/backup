<?php

declare(strict_types=1);

namespace App\Tests\Workflow\Handler\Service;

use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Workflow\Handler\Service\CustomerServiceRecord\AssignedToPendingHandler;
use App\Workflow\WorkflowStatusUpdater;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AssignedToPendingHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testSupported()
    {
        $current = new CustomerServiceRecord();
        $current->plannedAt = null;
        $current->leader = null;

        $previous = new CustomerServiceRecord();
        $previous->setStatus(AbstractCustomerServiceRecord::ASSIGNED);

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $assignedHandler = new AssignedToPendingHandler($workflowStatusUpdaterProphecy->reveal());

        $support = $assignedHandler->support($current, $previous);
        self::assertTrue($support);
    }

    /** @dataProvider notSupportedProvider */
    public function testNotSupported(object $current, ?object $previous)
    {
        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $assignedHandler = new AssignedToPendingHandler($workflowStatusUpdaterProphecy->reveal());

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
        $previous->setStatus(AbstractCustomerServiceRecord::ASSIGNED);

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $assignedHandler = new AssignedToPendingHandler($workflowStatusUpdaterProphecy->reveal());

        $support = $assignedHandler->support($current, $previous);
        // Verify first data set is supported
        self::assertTrue($support);

        yield 'Class not supported' => [new \stdClass(), $previous];

        $previous->setStatus('PLANNED');
        yield 'Status not supported' => [$current, $previous];

        $previous->setStatus(AbstractCustomerServiceRecord::ASSIGNED);
        $current->plannedAt = new \DateTime();
        yield 'No planned date' => [$current, $previous];

        $current->plannedAt = null;
        $current->leader = new People();
        yield 'No leader' => [$current, $previous];
    }

    public function testHandle()
    {
        $openIntervention = new Intervention();

        $current = new CustomerServiceRecord();
        $current->addIntervention($openIntervention);

        $previous = new CustomerServiceRecord();

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $workflowStatusUpdaterProphecy->applyStatus($openIntervention, Intervention::FAILED_ASSIGNEE)->shouldBeCalledOnce();
        $workflowStatusUpdaterProphecy->applyStatus($current, AbstractCustomerServiceRecord::PENDING)->shouldBeCalledOnce();

        $assignedHandler = new AssignedToPendingHandler($workflowStatusUpdaterProphecy->reveal());

        $assignedHandler->handle($current, $previous);
    }
}
