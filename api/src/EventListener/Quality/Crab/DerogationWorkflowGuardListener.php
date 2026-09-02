<?php

declare(strict_types=1);

namespace App\EventListener\Quality\Crab;

use App\Entity\Quality\Crab;
use App\Entity\Quality\Derogation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;

class DerogationWorkflowGuardListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.derogation.guard.to_open' => ['onReopen'],
        ];
    }

    public function onReopen(GuardEvent $event)
    {
        $derogation = $event->getSubject();

        if (!$derogation instanceof Derogation) {
            return;
        }

        $crab = $derogation->getCrabs()->first();

        if (Crab::CLOSED === $crab->status) {
            $event->addTransitionBlocker(new TransitionBlocker('Not possible to reopen a derogation on a closed CRAB.', TransitionBlocker::UNKNOWN));
        }
    }
}
