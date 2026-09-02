<?php

declare(strict_types=1);

namespace App\EventListener\Sales\Demo;

use App\Entity\Sales\Demo;
use App\Manager\Sales\DemoManager;
use LegacyBundle\Manager\SequenceManager;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class DemoWorkflowCompletedListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.demo.completed.to_active' => ['completedDemoToActive'],
            'workflow.demo.completed.to_approved' => ['completedDemoToApproved'],
            'workflow.demo.completed.to_rejected' => ['completedDemoToClosed'],
            'workflow.demo.completed.to_cancel' => ['completedDemoToClosed'],
            'workflow.demo.completed.to_unsuccessful' => ['completedDemoToClosed'],
            'workflow.demo.completed.to_successful_future_sale' => ['completedDemoToClosed'],
            'workflow.demo.completed.to_sold' => ['completedDemoToClosed'],
        ];
    }

    public function completedDemoToActive(CompletedEvent $event)
    {
        $demo = $event->getSubject();

        if (!$demo instanceof Demo) {
            return;
        }
        $demo->setActualStartDate(new \DateTime());
    }

    public function completedDemoToApproved(CompletedEvent $event)
    {
        $demo = $event->getSubject();

        if (!$demo instanceof Demo) {
            return;
        }

        $this->serviceLocator->get(DemoManager::class)->createTaskAndNotifyAssigneeWhenApproved($demo, $demo->getPsm());
    }

    public function completedDemoToClosed(CompletedEvent $event)
    {
        $demo = $event->getSubject();

        if (!$demo instanceof Demo) {
            return;
        }

        $demo->setDelinquent(false);
        $demo->setClosingDate(new \DateTime());

        if (Demo::CANCELLED === $demo->getStatus() && false !== ($sequence = $this->serviceLocator->get(SequenceManager::class)->findOpenSequence('sales.demo.approval', $demo->getId()))) {
            $this->serviceLocator->get(TaskManager::class)->close(
                (new Task())->setId((int) $sequence['id']),
                'This sequence has been automatically closed because the demo has been cancelled',
                $this->serviceLocator->get(Security::class)->getUser()
            );
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            DemoManager::class,
            SequenceManager::class,
            TaskManager::class,
            Security::class,
        ];
    }
}
