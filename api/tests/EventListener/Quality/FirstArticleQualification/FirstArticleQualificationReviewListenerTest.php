<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Entity\Quality\FirstArticleQualification\PlanItem;
use App\EventListener\Quality\FirstArticleQualification\FirstArticleQualificationReviewListener;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;

class FirstArticleQualificationReviewListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testReviewListenerDoesntImpactOtherClasses()
    {
        $event = new GuardEvent(new \stdClass(), new Marking(), new Transition('foo', [], []));

        $listener = new FirstArticleQualificationReviewListener();
        $listener->planCompletedGuardReview(new GuardEvent(new \stdClass(), new Marking(), new Transition('foo', [], [])));
        self::assertFalse($event->isBlocked());
    }

    public function testEventIsBlockedOnEmptyPlan()
    {
        $faqProphecy = $this->prophesize(FirstArticleQualification::class);
        $faqProphecy->getPlan()->willReturn(new ArrayCollection())->shouldBeCalledTimes(1);

        $event = new GuardEvent($faqProphecy->reveal(), new Marking(), new Transition('foo', [], []));

        $listener = new FirstArticleQualificationReviewListener();
        $listener->planCompletedGuardReview($event);
        self::assertTrue($event->isBlocked());
    }

    public function testEventIsNotBlockedOnInvalidPlanItem()
    {
        $planItem = new PlanItem();
        $faqProphecy = $this->prophesize(FirstArticleQualification::class);
        $faqProphecy->getPlan()->willReturn(new ArrayCollection([$planItem]))->shouldBeCalledTimes(1);
        $faqProphecy->getPlanApprovalStatus()->willReturn(FirstArticleQualification::APPROVED)->shouldBeCalledTimes(1);

        $event = new GuardEvent($faqProphecy->reveal(), new Marking(), new Transition('foo', [], []));

        $listener = new FirstArticleQualificationReviewListener();
        $listener->planCompletedGuardReview($event);
        self::assertFalse($event->isBlocked());
    }

    public function testEventIsNotBlockedOnValidPlan()
    {
        $faqProphecy = $this->prophesize(FirstArticleQualification::class);
        $faqProphecy->getPlan()->willReturn(new ArrayCollection([new PlanItem()]))->shouldBeCalledTimes(1);
        $faqProphecy->getPlanApprovalStatus()->willReturn(FirstArticleQualification::APPROVED)->shouldBeCalledTimes(1);

        $event = new GuardEvent($faqProphecy->reveal(), new Marking(), new Transition('foo', [], []));
        $listener = new FirstArticleQualificationReviewListener();
        $listener->planCompletedGuardReview($event);
        self::assertFalse($event->isBlocked());
    }

    public function testEventIsBlockedOnUnapprovedPlan()
    {
        $planItemProphecy = $this->prophesize(PlanItem::class);
        $faqProphecy = $this->prophesize(FirstArticleQualification::class);
        $faqProphecy->getPlan()->willReturn(new ArrayCollection([$planItemProphecy->reveal(), $planItemProphecy->reveal()]))->shouldBeCalledTimes(1);
        $faqProphecy->getPlanApprovalStatus()->willReturn(FirstArticleQualification::UNAPPROVED)->shouldBeCalledTimes(1);

        $event = new GuardEvent($faqProphecy->reveal(), new Marking(), new Transition('foo', [], []));
        $listener = new FirstArticleQualificationReviewListener();
        $listener->planCompletedGuardReview($event);
        self::assertTrue($event->isBlocked());
    }

    public function testInProgressIsBlockOnCompletedPlan()
    {
        $planItemProphecy = $this->prophesize(PlanItem::class);
        $planItemProphecy->getCompletionRate()->willReturn(100)->shouldBeCalledTimes(2);
        $faqProphecy = $this->prophesize(FirstArticleQualification::class);
        $faqProphecy->getPlan()->willReturn(new ArrayCollection([$planItemProphecy->reveal(), $planItemProphecy->reveal()]))->shouldBeCalledTimes(1);

        $event = new GuardEvent($faqProphecy->reveal(), new Marking(), new Transition('foo', [], []));
        $listener = new FirstArticleQualificationReviewListener();
        $listener->inProgressGuardReview($event);
        self::assertTrue($event->isBlocked());
    }

    public function testInProgressIsNotBlockedOnEmptyPlan()
    {
        $faqProphecy = $this->prophesize(FirstArticleQualification::class);
        $faqProphecy->getPlan()->willReturn(new ArrayCollection())->shouldBeCalledTimes(1);

        $event = new GuardEvent($faqProphecy->reveal(), new Marking(), new Transition('foo', [], []));
        $listener = new FirstArticleQualificationReviewListener();
        $listener->inProgressGuardReview($event);
        self::assertFalse($event->isBlocked());
    }

    public function testInProgressIsNotBlockedOnUncompletedPlan()
    {
        $planItemProphecy = $this->prophesize(PlanItem::class);
        $planItemProphecy->getCompletionRate()->willReturn(100)->shouldBeCalledTimes(1);
        $planItem = new PlanItem();
        $faqProphecy = $this->prophesize(FirstArticleQualification::class);
        $faqProphecy->getPlan()->willReturn(new ArrayCollection([$planItemProphecy->reveal(), $planItem]))->shouldBeCalledTimes(1);

        $event = new GuardEvent($faqProphecy->reveal(), new Marking(), new Transition('foo', [], []));
        $listener = new FirstArticleQualificationReviewListener();
        $listener->inProgressGuardReview($event);
        self::assertFalse($event->isBlocked());
    }
}
