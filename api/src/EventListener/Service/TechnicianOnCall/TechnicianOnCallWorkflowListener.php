<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use App\Entity\Service\TechnicianOnCall;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;

class TechnicianOnCallWorkflowListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.technician_on_call.completed.to_solved' => ['solved'],
        ];
    }

    public function solved(CompletedEvent $event): void
    {
        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $event->getSubject();
        $technicianOnCall->solvedAt = new \DateTime();

        if (null === $technicianOnCall->survey) {
            $technicianOnCall->token = bin2hex(random_bytes(32));
        }
    }
}
