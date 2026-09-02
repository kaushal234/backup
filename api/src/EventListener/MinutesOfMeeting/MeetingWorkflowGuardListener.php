<?php

declare(strict_types=1);

namespace App\EventListener\MinutesOfMeeting;

use App\Entity\MinutesOfMeeting\Action;
use App\Entity\MinutesOfMeeting\Meeting;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;

class MeetingWorkflowGuardListener implements EventSubscriberInterface
{
    public function guardClosed(GuardEvent $event)
    {
        $meeting = $event->getSubject();

        if (!$meeting instanceof Meeting) {
            return;
        }

        if (!$meeting->getActions()->filter(static fn (Action $action) => !$action->isCompleted())->isEmpty()) {
            $event->addTransitionBlocker(new TransitionBlocker('Some actions of the meeting are not completed.', TransitionBlocker::UNKNOWN));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.meeting.guard.to_closed' => ['guardClosed'],
        ];
    }
}
