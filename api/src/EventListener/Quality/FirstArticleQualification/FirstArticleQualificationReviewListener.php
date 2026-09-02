<?php

declare(strict_types=1);

namespace App\EventListener\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Entity\Quality\FirstArticleQualification\PlanItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class FirstArticleQualificationReviewListener implements EventSubscriberInterface
{
    public function planCompletedGuardReview(GuardEvent $event): void
    {
        /** @var FirstArticleQualification $firstArticleQualification */
        $firstArticleQualification = $event->getSubject();
        if (!$firstArticleQualification instanceof FirstArticleQualification) {
            return;
        }

        if ($firstArticleQualification->getPlan()->isEmpty()
            || FirstArticleQualification::APPROVED !== $firstArticleQualification->getPlanApprovalStatus()) {
            $event->setBlocked(true);
        }
    }

    public function inProgressGuardReview(GuardEvent $event): void
    {
        /** @var FirstArticleQualification $firstArticleQualification */
        $firstArticleQualification = $event->getSubject();
        if (!$firstArticleQualification instanceof FirstArticleQualification) {
            return;
        }
        if (($plan = $firstArticleQualification->getPlan())->isEmpty()) {
            return;
        }

        if ($plan->forAll(static fn ($key, PlanItem $planItem) => 100 === $planItem->getCompletionRate())) {
            $event->setBlocked(true);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.first_article_qualification.guard.to_conditional' => ['planCompletedGuardReview'],
            'workflow.first_article_qualification.guard.to_qualified' => ['planCompletedGuardReview'],
            'workflow.first_article_qualification.guard.to_in_progress' => ['inProgressGuardReview'],
        ];
    }
}
