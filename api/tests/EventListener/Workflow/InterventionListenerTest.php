<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Workflow;

use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\EventListener\Service\InterventionWorkflowListener;
use App\Workflow\WorkflowStatusUpdater;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Marking;

class InterventionListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testStart()
    {
        $customerServiceRecord = new CustomerServiceRecord();
        $intervention = new Intervention();
        $intervention->customerServiceRecord = $customerServiceRecord;

        $completedEvent = new CompletedEvent($intervention, new Marking());

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $interventionListener = new InterventionWorkflowListener($serviceLocatorProphecy->reveal());

        $interventionListener->start($completedEvent);

        self::assertSame(CustomerServiceRecord::IN_PROGRESS, $intervention->customerServiceRecord->getStatus());
    }

    public function testSolved(): void
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $workflowProphecy = $this->prophesize(WorkflowStatusUpdater::class);

        $customerServiceRecord = new CustomerServiceRecord();
        $intervention = new Intervention();
        $intervention->customerServiceRecord = $customerServiceRecord;

        $completedEvent = new CompletedEvent($intervention, new Marking());
        $workflowProphecy->applyStatus($customerServiceRecord, AbstractCustomerServiceRecord::COMPLETED, ['interventionEndedAt' => null])->shouldBeCalledOnce();
        $serviceLocatorProphecy->get(WorkflowStatusUpdater::class)->willReturn($workflowProphecy->reveal());

        $listener = new InterventionWorkflowListener($serviceLocatorProphecy->reveal());
        $listener->solved($completedEvent);
    }

    public function testSolvedPassesEndedAtContextToApplyStatus(): void
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $workflowProphecy = $this->prophesize(WorkflowStatusUpdater::class);

        $commissioningRecord = new CommissioningCustomerServiceRecord();

        $intervention = new Intervention();
        $intervention->endedAt = new \DateTime('2024-03-12 14:00:00');
        $intervention->customerServiceRecord = $commissioningRecord;

        $completedEvent = new CompletedEvent($intervention, new Marking());
        $workflowProphecy->applyStatus($commissioningRecord, AbstractCustomerServiceRecord::COMPLETED, ['interventionEndedAt' => $intervention->endedAt])->shouldBeCalledOnce();
        $serviceLocatorProphecy->get(WorkflowStatusUpdater::class)->willReturn($workflowProphecy->reveal());

        $listener = new InterventionWorkflowListener($serviceLocatorProphecy->reveal());
        $listener->solved($completedEvent);
    }

    public function testToContinue()
    {
        $customerServiceRecord = new CustomerServiceRecord();
        $intervention = new Intervention();
        $intervention->customerServiceRecord = $customerServiceRecord;

        $completedEvent = new CompletedEvent($intervention, new Marking());

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $interventionListener = new InterventionWorkflowListener($serviceLocatorProphecy->reveal());

        $interventionListener->toContinue($completedEvent);

        self::assertSame(CustomerServiceRecord::PENDING, $intervention->customerServiceRecord->getStatus());
    }

    public function testChange()
    {
        $customerServiceRecord = new CustomerServiceRecord();
        $intervention = new Intervention();
        $intervention->customerServiceRecord = $customerServiceRecord;

        $completedEvent = new CompletedEvent($intervention, new Marking());

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledOnce()->willReturn(new People());

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledOnce()->willReturn($securityProphecy->reveal());
        $interventionListener = new InterventionWorkflowListener($serviceLocatorProphecy->reveal());

        $interventionListener->onChange($completedEvent);

        self::assertInstanceOf(People::class, $intervention->plannedBy);
    }
}
