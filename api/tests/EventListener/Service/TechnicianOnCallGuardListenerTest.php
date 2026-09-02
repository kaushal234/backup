<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Service;

use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\TechnicianOnCallCustomerServiceRecord;
use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallNotifier;
use App\Workflow\WorkflowStatusUpdater;
use Doctrine\ORM\EntityRepository;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Workflow\Exception\LogicException;

class TechnicianOnCallGuardListenerTest extends KernelTestCase
{
    use ProphecyTrait;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        self::bootKernel(['environment' => 'test', 'debug' => false]);
        $this->container = static::getContainer();

        $notifierMock = $this->createMock(TechnicianOnCallNotifier::class);
        $this->container->set(TechnicianOnCallNotifier::class, $notifierMock);
    }

    public function testCannotSolvedTechnicianOnCallIfCustomerServiceRecordIsOpen(): void
    {
        $this->loadSuperUser();
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Status SOLVED is not allowed. Reasons: A CSR is already open.');

        $customerServiceRecord = new TechnicianOnCallCustomerServiceRecord();

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->setStatus('IN_PROGRESS');
        $technicianOnCall->addCustomerServiceRecord($customerServiceRecord);

        /** @var WorkflowStatusUpdater $stateMachine */
        $stateMachine = $this->container->get(WorkflowStatusUpdater::class);

        $stateMachine->applyStatus($technicianOnCall, 'SOLVED');

        self::assertNotSame('SOLVED', $technicianOnCall->getStatus());
    }

    public function testTechnicianOnCallCanBeSetToSolved(): void
    {
        $this->loadSuperUser();
        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->setStatus('IN_PROGRESS');

        /** @var WorkflowStatusUpdater $stateMachine */
        $stateMachine = $this->container->get(WorkflowStatusUpdater::class);

        $stateMachine->applyStatus($technicianOnCall, 'SOLVED');

        self::assertSame('SOLVED', $technicianOnCall->getStatus());
    }

    public function testTechnicianOnCallCannotBeSolvedForBasicUser(): void
    {
        $this->loadBasicUser();
        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->setStatus('IN_PROGRESS');

        /** @var WorkflowStatusUpdater $stateMachine */
        $stateMachine = $this->container->get(WorkflowStatusUpdater::class);

        $stateMachine->applyStatus($technicianOnCall, 'SOLVED');

        self::assertSame('SOLVED', $technicianOnCall->getStatus());
    }

    public function testTechnicianOnCallCannotBeSolvedWithFactoryFlag(): void
    {
        $this->loadSuperUser();
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Status SOLVED is not allowed. Reasons: TOC should not be SOLVED, the Factory Flag is still open');

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->setStatus('IN_PROGRESS');
        $technicianOnCall->factoryFlag = true;

        /** @var WorkflowStatusUpdater $stateMachine */
        $stateMachine = $this->container->get(WorkflowStatusUpdater::class);

        $stateMachine->applyStatus($technicianOnCall, 'SOLVED');
    }

    private function loadSuperUser(): void
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(People::class);
        $user = $repository->findOneBy(['email' => 'user-superuser@tld.fr']);

        $tokenStorage = $this->container->get(TokenStorageInterface::class);
        $token = new UsernamePasswordToken($user, 'default');
        $tokenStorage->setToken($token);
    }

    private function loadBasicUser(): void
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(People::class);
        $user = $repository->findOneBy(['email' => 'user-basic@tld.fr']);

        $tokenStorage = $this->container->get(TokenStorageInterface::class);
        $token = new UsernamePasswordToken($user, 'default');
        $tokenStorage->setToken($token);
    }
}
