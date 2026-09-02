<?php

declare(strict_types=1);

namespace App\EventListener\Service;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Workflow\WorkflowStatusUpdater;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class InterventionWorkflowListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.intervention.completed.to_started' => ['start'],
            'workflow.intervention.completed.to_solved' => ['solved'],
            'workflow.intervention.completed.to_to_continue' => ['toContinue'],
            'workflow.intervention.completed' => ['onChange'],
        ];
    }

    public function start(CompletedEvent $event): void
    {
        $event->getSubject()->customerServiceRecord->setStatus(AbstractCustomerServiceRecord::IN_PROGRESS);
    }

    public function solved(CompletedEvent $event): void
    {
        /** @var Intervention $intervention */
        $intervention = $event->getSubject();
        $customerServiceRecord = $intervention->customerServiceRecord;
        $workflow = $this->serviceLocator->get(WorkflowStatusUpdater::class);
        $workflow->applyStatus($customerServiceRecord, AbstractCustomerServiceRecord::COMPLETED, [
            'interventionEndedAt' => $intervention->endedAt,
        ]);
    }

    public function toContinue(CompletedEvent $event): void
    {
        $event->getSubject()->customerServiceRecord->setStatus(AbstractCustomerServiceRecord::PENDING);
        $event->getSubject()->customerServiceRecord->plannedAt = null;
    }

    public function onChange(CompletedEvent $event): void
    {
        $security = $this->serviceLocator->get(Security::class);
        $user = $security->getUser();
        $event->getSubject()->plannedBy = $user;
    }

    public static function getSubscribedServices(): array
    {
        return [
            Security::class,
            WorkflowStatusUpdater::class,
        ];
    }
}
