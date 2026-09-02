<?php

declare(strict_types=1);

namespace App\Tests\Workflow\Handler\Service;

use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Workflow\Handler\Service\CustomerServiceRecord\AssignedToAnotherHandler;
use PHPUnit\Framework\TestCase;

class AssignedToAnotherHandlerTest extends TestCase
{
    public function testSupported()
    {
        $openIntervention = new Intervention();
        $openIntervention->setStatus(Intervention::PENDING);
        $openIntervention->leader = new People();
        $openIntervention->plannedAt = new \DateTime();

        $current = new CustomerServiceRecord();
        $current->plannedAt = new \DateTime();
        $current->leader = new People();

        $previous = new CustomerServiceRecord();
        $previous->setStatus(CustomerServiceRecord::ASSIGNED);
        $previous->addIntervention($openIntervention);

        $assignedHandler = new AssignedToAnotherHandler();

        $support = $assignedHandler->support($current, $previous);
        self::assertTrue($support);
    }

    /** @dataProvider notSupportedProvider */
    public function testNotSupported(object $current, ?object $previous)
    {
        $assignedHandler = new AssignedToAnotherHandler();

        $support = $assignedHandler->support($current, $previous);
        self::assertFalse($support);
    }

    public function notSupportedProvider()
    {
        yield 'No previous data' => [new \stdClass(), null];

        $openIntervention = new Intervention();
        $openIntervention->setStatus(Intervention::PENDING);
        $openIntervention->leader = new People();
        $openIntervention->plannedAt = new \DateTime();

        $current = new CustomerServiceRecord();
        $current->plannedAt = new \DateTime();
        $current->leader = new People();

        $previous = new CustomerServiceRecord();
        $previous->setStatus(CustomerServiceRecord::ASSIGNED);
        $previous->addIntervention($openIntervention);

        $assignedHandler = new AssignedToAnotherHandler();

        $support = $assignedHandler->support($current, $previous);
        self::assertTrue($support);

        yield 'Class not supported' => [new \stdClass(), $previous];

        $previous->setStatus('PLANNED');
        yield 'Status not supported' => [$current, $previous];

        $previous->setStatus(CustomerServiceRecord::ASSIGNED);
        $current->plannedAt = null;
        yield 'No planned date' => [$current, $previous];

        $current->plannedAt = new \DateTime('+10 days');
        yield 'Previous and current have different date' => [$current, $previous];

        $current->plannedAt = new \DateTime();
        $current->leader = null;
        yield 'No leader on current object' => [$current, $previous];

        $leader = new People();
        $current->leader = $leader;
        $openIntervention->leader = $leader;
        yield 'Same leader on current and previous object' => [$current, $previous];
    }

    public function testHandle()
    {
        $oldLeader = new People();
        $openIntervention = new Intervention();
        $openIntervention->leader = $oldLeader;

        $newLeader = new People();
        $current = new CustomerServiceRecord();
        $current->leader = $newLeader;
        $current->addIntervention($openIntervention);

        $previous = new CustomerServiceRecord();

        $assignedHandler = new AssignedToAnotherHandler();

        $assignedHandler->handle($current, $previous);

        self::assertSame($newLeader, $current->getOpenIntervention()->leader);
        self::assertNotSame($oldLeader, $current->getOpenIntervention()->leader);
    }
}
