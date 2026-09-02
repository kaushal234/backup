<?php

declare(strict_types=1);

namespace App\EventListener\Sales\Demo;

use App\Entity\Directory\People;
use App\Entity\Sales\Demo;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class DemoWorkflowGuardListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.demo.guard.to_closed_cancel' => ['guardDemoSecuredTransitions'],
            'workflow.demo.guard.to_closed_unsuccessful' => ['guardDemoSecuredTransitions'],
            'workflow.demo.guard.to_closed_rejected' => ['guardDemoSecuredTransitions'],
            'workflow.demo.guard.to_closed_successful' => ['guardDemoSecuredTransitions'],
            'workflow.demo.guard.to_closed_successful_future_sale' => ['guardDemoSecuredTransitions'],
            'workflow.demo.guard.to_cancel' => ['guardDemoSecuredTransitions'],
        ];
    }

    public function guardDemoSecuredTransitions(GuardEvent $event)
    {
        $demo = $event->getSubject();

        if (!$demo instanceof Demo) {
            return;
        }

        $security = $this->serviceLocator->get(Security::class);
        if (null === ($user = $security->getUser()) || !$user instanceof People) {
            return;
        }

        if ($security->isGranted('MOO_DEMO')) {
            return;
        }

        if ($security->isGranted('FEATURE_DEMO_ADMIN', $demo)) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public static function getSubscribedServices(): array
    {
        return [
            Security::class,
        ];
    }
}
