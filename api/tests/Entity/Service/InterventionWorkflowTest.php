<?php

declare(strict_types=1);

namespace App\Tests\Entity\Service;

use App\Entity\Service\CustomerServiceRecord\Intervention;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Workflow\WorkflowInterface;

class InterventionWorkflowTest extends KernelTestCase
{
    private $places;
    private $initialPlaces;
    private $transitions;
    private WorkflowInterface $stateMachine;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->stateMachine = $container->get('state_machine.intervention');
        $this->places = $this->stateMachine->getDefinition()->getPlaces();
        $this->initialPlaces = $this->stateMachine->getDefinition()->getInitialPlaces();
        $this->transitions = $this->stateMachine->getDefinition()->getTransitions();
    }

    /** @dataProvider dataProviderStateMachine */
    public function testWorkflow(string $currentStatus, string $transitionName, bool $authorized)
    {
        $intervention = new Intervention();
        $intervention->endedAt = new \DateTime();
        $intervention->setStatus($currentStatus);

        if ($authorized) {
            self::assertTrue($this->stateMachine->can($intervention, $transitionName));
        } else {
            self::assertFalse($this->stateMachine->can($intervention, $transitionName));
        }
    }

    public function dataProviderStateMachine()
    {
        yield 'Test transition from pending to started' => ['PENDING', 'to_started', true];
        yield 'Test transition from pending to solved' => ['PENDING', 'to_solved', true];
        yield 'Test transition from pending to to continue' => ['PENDING', 'to_to_continue', true];
        yield 'Test transition from pending to unschedule' => ['PENDING', 'to_unschedule', true];

        yield 'Test transition from failed_assigned to started' => ['FAILED_ASSIGNEE', 'to_started', true];
        yield 'Test transition from failed_assigned to solved' => ['FAILED_ASSIGNEE', 'to_solved', true];
        yield 'Test transition from failed_assigned to to_continue' => ['FAILED_ASSIGNEE', 'to_to_continue', true];
        yield 'Test transition from failed_assigned to unschedule' => ['FAILED_ASSIGNEE', 'to_unschedule', false];

        yield 'Test transition from started to started' => ['STARTED', 'to_started', false];
        yield 'Test transition from started to solved' => ['STARTED', 'to_solved', true];
        yield 'Test transition from started to to_continue' => ['STARTED', 'to_to_continue', true];
        yield 'Test transition from started to unschedule' => ['STARTED', 'to_unschedule', false];

        yield 'Test transition from solved to started' => ['SOLVED', 'to_started', false];
        yield 'Test transition from solved to solved' => ['SOLVED', 'to_solved', false];
        yield 'Test transition from solved to to_continue' => ['SOLVED', 'to_to_continue', false];
        yield 'Test transition from solved to unschedule' => ['SOLVED', 'to_unschedule', false];

        yield 'Test transition from to_continue to started' => ['TO_CONTINUE', 'to_started', false];
        yield 'Test transition from to_continue to solved' => ['TO_CONTINUE', 'to_solved', false];
        yield 'Test transition from to_continue to to_continue' => ['TO_CONTINUE', 'to_to_continue', false];
        yield 'Test transition from to_continue to unschedule' => ['TO_CONTINUE', 'to_unschedule', false];
    }

    public function testPlaces()
    {
        $arrayPlacesExpected = [
            'PENDING' => 'PENDING',
            'FAILED_ASSIGNEE' => 'FAILED_ASSIGNEE',
            'STARTED' => 'STARTED',
            'SOLVED' => 'SOLVED',
            'TO_CONTINUE' => 'TO_CONTINUE',
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
        self::assertCount(9, $this->transitions);
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
        yield 'Test transition from PENDING to started' => [0, 'to_started', 'PENDING', 'STARTED'];
        yield 'Test transition from FAILED_ASSIGNEE to started' => [1, 'to_started', 'FAILED_ASSIGNEE', 'STARTED'];
        yield 'Test transition to unschedule' => [2, 'to_unschedule', 'PENDING', 'FAILED_ASSIGNEE'];
        yield 'Test transition from PENDING to solved' => [3, 'to_solved', 'PENDING', 'SOLVED'];
        yield 'Test transition from STARTED to solved' => [4, 'to_solved', 'STARTED', 'SOLVED'];
        yield 'Test transition from FAILED_ASSIGNEE to solved' => [5, 'to_solved', 'FAILED_ASSIGNEE', 'SOLVED'];
        yield 'Test transition from PENDING to to continue' => [6, 'to_to_continue', 'PENDING', 'TO_CONTINUE'];
        yield 'Test transition from STARTED to to continue' => [7, 'to_to_continue', 'STARTED', 'TO_CONTINUE'];
        yield 'Test transition from FAILED_ASSIGNEE to to continue' => [8, 'to_to_continue', 'FAILED_ASSIGNEE', 'TO_CONTINUE'];
    }
}
