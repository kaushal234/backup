<?php

declare(strict_types=1);

namespace App\Tests\Entity\Service;

use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Workflow\WorkflowInterface;

class CustomerServiceRecordWorkflowTest extends KernelTestCase
{
    private $places;
    private $initialPlaces;
    private $transitions;
    private WorkflowInterface $stateMachine;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->stateMachine = $container->get('state_machine.customer_service_record');
        $this->places = $this->stateMachine->getDefinition()->getPlaces();
        $this->initialPlaces = $this->stateMachine->getDefinition()->getInitialPlaces();
        $this->transitions = $this->stateMachine->getDefinition()->getTransitions();
    }

    /** @dataProvider dataProviderStateMachine */
    public function testWorkflow(string $currentStatus, string $transitionName, bool $authorized)
    {
        $csr = new CustomerServiceRecord();
        $csr->setStatus($currentStatus);

        if ($authorized) {
            self::assertTrue($this->stateMachine->can($csr, $transitionName));
        } else {
            self::assertFalse($this->stateMachine->can($csr, $transitionName));
        }
    }

    public function dataProviderStateMachine()
    {
        yield 'Test transition from pending to pending' => ['PENDING', 'to_pending', false];
        yield 'Test transition from pending to planned' => ['PENDING', 'to_planned', true];
        yield 'Test transition from pending to assigned' => ['PENDING', 'to_assigned', true];
        yield 'Test transition from pending to in progress' => ['PENDING', 'to_in_progress', false];
        yield 'Test transition from pending to completed' => ['PENDING', 'to_completed', false];
        yield 'Test transition from pending to closed' => ['PENDING', 'to_closed', false];

        yield 'Test transition from planned to pending' => ['PLANNED', 'to_pending', true];
        yield 'Test transition from planned to planned' => ['PLANNED', 'to_planned', false];
        yield 'Test transition from planned to assigned' => ['PLANNED', 'to_assigned', true];
        yield 'Test transition from planned to in progress' => ['PLANNED', 'to_in_progress', false];
        yield 'Test transition from planned to completed' => ['PLANNED', 'to_completed', false];
        yield 'Test transition from planned to closed' => ['PLANNED', 'to_closed', false];

        yield 'Test transition from assigned to pending' => ['ASSIGNED', 'to_pending', true];
        yield 'Test transition from assigned to planned' => ['ASSIGNED', 'to_planned', true];
        yield 'Test transition from assigned to assigned' => ['ASSIGNED', 'to_assigned', false];
        yield 'Test transition from assigned to in progress' => ['ASSIGNED', 'to_in_progress', true];
        yield 'Test transition from assigned to completed' => ['ASSIGNED', 'to_completed', true];
        yield 'Test transition from assigned to closed' => ['ASSIGNED', 'to_closed', false];

        yield 'Test transition from in progress to pending' => ['IN-PROGRESS', 'to_pending', false];
        yield 'Test transition from in progress to planned' => ['IN-PROGRESS', 'to_planned', false];
        yield 'Test transition from in progress to assigned' => ['IN-PROGRESS', 'to_assigned', false];
        yield 'Test transition from in progress to in progress' => ['IN-PROGRESS', 'to_in_progress', false];
        yield 'Test transition from in progress to completed' => ['IN-PROGRESS', 'to_completed', true];
        yield 'Test transition from in progress to closed' => ['IN-PROGRESS', 'to_closed', false];

        yield 'Test transition from completed to pending' => ['COMPLETED', 'to_pending', true];
        yield 'Test transition from completed to planned' => ['COMPLETED', 'to_planned', false];
        yield 'Test transition from completed to assigned' => ['COMPLETED', 'to_assigned', false];
        yield 'Test transition from completed to in progress' => ['COMPLETED', 'to_in_progress', false];
        yield 'Test transition from completed to completed' => ['COMPLETED', 'to_completed', false];
        yield 'Test transition from completed to closed' => ['COMPLETED', 'to_closed', true];

        yield 'Test transition from closed to pending' => ['CLOSED', 'to_pending', true];
        yield 'Test transition from closed to planned' => ['CLOSED', 'to_planned', false];
        yield 'Test transition from closed to assigned' => ['CLOSED', 'to_assigned', false];
        yield 'Test transition from closed to in progress' => ['CLOSED', 'to_in_progress', false];
        yield 'Test transition from closed to completed' => ['CLOSED', 'to_completed', true];
        yield 'Test transition from closed to closed' => ['CLOSED', 'to_closed', false];
    }

    public function testPlaces()
    {
        $arrayPlacesExpected = [
            'PENDING' => 'PENDING',
            'PLANNED' => 'PLANNED',
            'ASSIGNED' => 'ASSIGNED',
            'IN-PROGRESS' => 'IN-PROGRESS',
            'COMPLETED' => 'COMPLETED',
            'CLOSED' => 'CLOSED',
        ];
        self::assertSame($arrayPlacesExpected, $this->places);
    }

    public function testInitialPlaces()
    {
        self::assertCount(1, $this->initialPlaces);
        self::assertSame('PENDING', $this->initialPlaces[0]);
    }

    public function testTransitionCount()
    {
        self::assertCount(13, $this->transitions);
    }

    /** @dataProvider dataProviderStateMachineCustomerServiceRecord */
    public function testTransitions($transitionCount, $nameExpected, $fromsExpected, $tosExpected)
    {
        $transition = $this->transitions[$transitionCount];
        self::assertSame($nameExpected, $transition->getName());
        self::assertCount(1, $transition->getFroms());
        self::assertSame($fromsExpected, $transition->getFroms()[0]);
        self::assertCount(1, $transition->getTos());
        self::assertSame($tosExpected, $transition->getTos()[0]);
    }

    public function dataProviderStateMachineCustomerServiceRecord()
    {
        yield 'Test transition from pending to planned' => [0, 'to_planned', 'PENDING', 'PLANNED'];
        yield 'Test transition from assigned to planned' => [1, 'to_planned', 'ASSIGNED', 'PLANNED'];
        yield 'Test transition from pending to assigned' => [2, 'to_assigned', 'PENDING', 'ASSIGNED'];
        yield 'Test transition from planned to assigned' => [3, 'to_assigned', 'PLANNED', 'ASSIGNED'];
        yield 'Test transition to in_progress' => [4, 'to_in_progress', 'ASSIGNED', 'IN-PROGRESS'];
        yield 'Test transition from closed to completed' => [5, 'to_completed', 'CLOSED', 'COMPLETED'];
        yield 'Test transition from in progress to completed' => [6, 'to_completed', 'IN-PROGRESS', 'COMPLETED'];
        yield 'Test transition from ASSIGNED to COMPLETED' => [7, 'to_completed', 'ASSIGNED', 'COMPLETED'];
        yield 'Test transition from CLOSED to pending' => [8, 'to_pending', 'CLOSED', 'PENDING'];
        yield 'Test transition from PLANNED to pending' => [9, 'to_pending', 'PLANNED', 'PENDING'];
        yield 'Test transition from ASSIGNED to pending' => [10, 'to_pending', 'ASSIGNED', 'PENDING'];
        yield 'Test transition from COMPLETED to pending' => [11, 'to_pending', 'COMPLETED', 'PENDING'];
        yield 'Test transition to closed' => [12, 'to_closed', 'COMPLETED', 'CLOSED'];
    }
}
