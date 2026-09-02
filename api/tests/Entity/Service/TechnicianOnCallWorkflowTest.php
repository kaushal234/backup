<?php

declare(strict_types=1);

namespace App\Tests\Entity\Service;

use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Workflow\WorkflowInterface;

class TechnicianOnCallWorkflowTest extends KernelTestCase
{
    private WorkflowInterface $stateMachine;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->stateMachine = $container->get('state_machine.technician_on_call');

        $entityManager = $container->get('doctrine.orm.entity_manager');
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(People::class);
        $user = $repository->findOneBy(['email' => 'user-superuser@tld.fr']);

        $tokenStorage = $container->get(TokenStorageInterface::class);
        $token = new UsernamePasswordToken($user, 'default', []);
        $tokenStorage->setToken($token);
    }

    /** @dataProvider dataProviderStateMachine */
    public function testWorkflow(string $currentStatus, string $transitionName, bool $authorized): void
    {
        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->setStatus($currentStatus);

        if ($authorized) {
            self::assertTrue($this->stateMachine->can($technicianOnCall, $transitionName));
        } else {
            self::assertFalse($this->stateMachine->can($technicianOnCall, $transitionName));
        }
    }

    public function dataProviderStateMachine()
    {
        // Transition to_in_progress
        yield 'Test transition from PENDING to IN_PROGRESS' => [TechnicianOnCall::PENDING, 'to_in_progress', true];
        yield 'Test transition from IN_PROGRESS to IN_PROGRESS' => [TechnicianOnCall::IN_PROGRESS, 'to_in_progress', false];
        yield 'Test transition from SOLVED to IN_PROGRESS' => [TechnicianOnCall::SOLVED, 'to_in_progress', true];
        yield 'Test transition from CLOSED to IN_PROGRESS' => [TechnicianOnCall::CLOSED, 'to_in_progress', true];
        yield 'Test transition from SUSPENDED to IN_PROGRESS' => [TechnicianOnCall::SUSPENDED, 'to_in_progress', true];

        // Transition to_solved
        yield 'Test transition from PENDING to SOLVED' => [TechnicianOnCall::PENDING, 'to_solved', false];
        yield 'Test transition from IN_PROGRESS to SOLVED' => [TechnicianOnCall::IN_PROGRESS, 'to_solved', true];
        yield 'Test transition from SOLVED to SOLVED' => [TechnicianOnCall::SOLVED, 'to_solved', false];
        yield 'Test transition from CLOSED to SOLVED' => [TechnicianOnCall::CLOSED, 'to_solved', false];
        yield 'Test transition from SUSPENDED to SOLVED' => [TechnicianOnCall::SUSPENDED, 'to_solved', true];

        // Transition to_closed
        yield 'Test transition from PENDING to CLOSED' => [TechnicianOnCall::PENDING, 'to_closed', false];
        yield 'Test transition from IN_PROGRESS to CLOSED' => [TechnicianOnCall::IN_PROGRESS, 'to_closed', false];
        yield 'Test transition from SOLVED to CLOSED' => [TechnicianOnCall::SOLVED, 'to_closed', false];
        yield 'Test transition from CLOSED to CLOSED' => [TechnicianOnCall::CLOSED, 'to_closed', false];
        yield 'Test transition from SUSPENDED to CLOSED' => [TechnicianOnCall::SUSPENDED, 'to_closed', false];

        // Transition to_suspended
        yield 'Test transition from PENDING to SUSPENDED' => [TechnicianOnCall::PENDING, 'to_suspended', false];
        yield 'Test transition from IN_PROGRESS to SUSPENDED' => [TechnicianOnCall::IN_PROGRESS, 'to_suspended', true];
        yield 'Test transition from SOLVED to SUSPENDED' => [TechnicianOnCall::SOLVED, 'to_suspended', false];
        yield 'Test transition from CLOSED to SUSPENDED' => [TechnicianOnCall::CLOSED, 'to_suspended', false];
        yield 'Test transition from SUSPENDED to SUSPENDED' => [TechnicianOnCall::SUSPENDED, 'to_suspended', false];
    }

    public function testPlaces()
    {
        $arrayPlacesExpected = [
            'PENDING' => 'PENDING',
            'IN_PROGRESS' => 'IN_PROGRESS',
            'SOLVED' => 'SOLVED',
            'CLOSED' => 'CLOSED',
            'SUSPENDED' => 'SUSPENDED',
        ];
        self::assertSame($arrayPlacesExpected, $this->stateMachine->getDefinition()->getPlaces());
    }

    public function testInitialPlaces()
    {
        $initialPlaces = $this->stateMachine->getDefinition()->getInitialPlaces();
        self::assertCount(1, $initialPlaces);
        self::assertSame('PENDING', $initialPlaces[0]);
    }

    public function testTransitionCount()
    {
        self::assertCount(8, $this->stateMachine->getDefinition()->getTransitions());
    }

    /** @dataProvider dataProviderStateMachineTransitions */
    public function testTransitions($transitionCount, $nameExpected, $fromsExpected, $tosExpected, $last)
    {
        $transition = $this->stateMachine->getDefinition()->getTransitions()[$transitionCount];
        self::assertSame($nameExpected, $transition->getName());
        self::assertCount(1, $transition->getFroms());
        self::assertSame($fromsExpected, $transition->getFroms()[0]);
        self::assertCount(1, $transition->getTos());
        self::assertSame($tosExpected, $transition->getTos()[0]);

        if ($last) {
            self::assertCount($transitionCount + 1, $this->stateMachine->getDefinition()->getTransitions());
        }
    }

    public function dataProviderStateMachineTransitions()
    {
        $index = 0;
        // Transition to_in_progress
        yield 'Test transition from PENDING to IN_PROGRESS' => [$index++, 'to_in_progress', 'PENDING', 'IN_PROGRESS', false];
        yield 'Test transition from SOLVED to IN_PROGRESS' => [$index++, 'to_in_progress', 'SOLVED', 'IN_PROGRESS', false];
        yield 'Test transition from CLOSED to IN_PROGRESS' => [$index++, 'to_in_progress', 'CLOSED', 'IN_PROGRESS', false];
        yield 'Test transition from SUSPENDED to IN_PROGRESS' => [$index++, 'to_in_progress', 'SUSPENDED', 'IN_PROGRESS', false];

        // Transition to_solved
        yield 'Test transition from SUSPENDED to SOLVED' => [$index++, 'to_solved', 'SUSPENDED', 'SOLVED', false];
        yield 'Test transition from IN_PROGRESS to SOLVED' => [$index++, 'to_solved', 'IN_PROGRESS', 'SOLVED', false];

        // Transition to_closed
        yield 'Test transition from SOLVED to CLOSED' => [$index++, 'to_closed', 'SOLVED', 'CLOSED', false];

        // Transition to_suspended
        yield 'Test transition from IN_PROGRESS to SUSPENDED' => [$index, 'to_suspended', 'IN_PROGRESS', 'SUSPENDED', true];
    }
}
