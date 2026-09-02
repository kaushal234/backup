<?php

declare(strict_types=1);

namespace App\EventListener\Service;

use App\Entity\Service\CustomerServiceRecord\Intervention;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;

class InterventionGuardListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.intervention.guard.to_solved' => ['guardClosed'],
            'workflow.intervention.guard.to_to_continue' => ['guardClosed'],
        ];
    }

    public function guardClosed(GuardEvent $event): void
    {
        if (!($intervention = $event->getSubject()) instanceof Intervention) {
            return;
        }

        if (null !== $intervention->endedAt) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('End date is required', TransitionBlocker::UNKNOWN));
    }
}
