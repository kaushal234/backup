<?php

declare(strict_types=1);

namespace App\Tests\EventListener\MinutesOfMeeting;

use App\Entity\MinutesOfMeeting\Action;
use App\Entity\MinutesOfMeeting\Meeting;
use App\EventListener\MinutesOfMeeting\MeetingWorkflowGuardListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;

class MeetingWorkflowGuardListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testListenerPreventsMeetingClosureWhenOneActionIsNotCompleted()
    {
        $meeting = (new Meeting())->addAction((new Action())->setCompleted(false));

        $event = new GuardEvent($meeting, new Marking(), new Transition('foo', [], []));

        $listener = new MeetingWorkflowGuardListener();
        $listener->guardClosed($event);

        self::assertTrue($event->isBlocked());
    }

    public function testListenerAuthorizeMeetingClosureWhenAllActionsAreCompleted()
    {
        $meeting = (new Meeting())->addAction((new Action())->setCompleted(true));

        $event = new GuardEvent($meeting, new Marking(), new Transition('foo', [], []));

        $listener = new MeetingWorkflowGuardListener();
        $listener->guardClosed($event);

        self::assertFalse($event->isBlocked());
    }
}
