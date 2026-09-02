<?php

declare(strict_types=1);

namespace App\Manager\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Entity\Quality\FirstArticleQualification\PlanItem;
use App\Exception\FirstArticleQualificationPlanDuplicationException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Fan-out clone of an approved qualification plan into one or more target FAQs.
 *
 * Business rules:
 *  - Source FAQ must be APPROVED, otherwise nothing runs.
 *  - Each target must (a) share the source's location (same factory) and
 *    (b) have an empty plan. All rejections are collected before any write.
 *  - All-or-nothing: any rejection cancels the whole operation.
 *  - Cloned items keep every field EXCEPT completionRate (reset to 0) and
 *    the per-item validation trail (validatedAt / validatedBy stay null).
 */
final readonly class QualificationPlanDuplicatorManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @param list<FirstArticleQualification> $targets
     *
     * @return list<FirstArticleQualification> the target FAQs, now carrying a fresh plan
     *
     * @throws FirstArticleQualificationPlanDuplicationException
     */
    public function duplicate(FirstArticleQualification $source, array $targets): array
    {
        if (FirstArticleQualification::APPROVED !== $source->getPlanApprovalStatus()) {
            throw FirstArticleQualificationPlanDuplicationException::sourceNotApproved();
        }

        $targets = $this->dedupe($targets);
        $rejections = $this->collectRejections($source, $targets);

        if ([] !== $rejections) {
            throw FirstArticleQualificationPlanDuplicationException::targetsRejected($rejections);
        }

        return $this->em->wrapInTransaction(function () use ($source, $targets): array {
            foreach ($targets as $target) {
                foreach ($source->getPlan() as $sourceItem) {
                    $target->setPlanDefinitionCompletedAt(null);
                    $target->setPlanApprovalStatus(FirstArticleQualification::NOT_APPROVED_YET);
                    $target->addPlan($this->cloneItem($sourceItem));
                }
            }
            $this->em->flush();

            return $targets;
        });
    }

    /**
     * The client may accidentally pass the same FAQ twice; without dedup, we'd
     * clone the plan twice into the same target.
     *
     * @param list<FirstArticleQualification> $targets
     *
     * @return list<FirstArticleQualification>
     */
    private function dedupe(array $targets): array
    {
        $out = [];
        foreach ($targets as $target) {
            if (!\in_array($target, $out, true)) {
                $out[] = $target;
            }
        }

        return $out;
    }

    /**
     * "target has plan" takes precedence over "different factory" so we report
     * at most one reason per FAQ.
     *
     * @param list<FirstArticleQualification> $targets
     *
     * @return list<array{faq: FirstArticleQualification, reason: string}>
     */
    private function collectRejections(FirstArticleQualification $source, array $targets): array
    {
        $rejections = [];
        foreach ($targets as $target) {
            if (!$target->getPlan()->isEmpty()) {
                $rejections[] = ['faq' => $target, 'reason' => FirstArticleQualificationPlanDuplicationException::REASON_TARGET_HAS_PLAN];

                continue;
            }
            if ($target->getLocation() !== $source->getLocation()) {
                $rejections[] = ['faq' => $target, 'reason' => FirstArticleQualificationPlanDuplicationException::REASON_DIFFERENT_FACTORY];
            }
        }

        return $rejections;
    }

    private function cloneItem(PlanItem $source): PlanItem
    {
        $clone = new PlanItem();
        $clone->setType($source->getType());
        $clone->setDescription($source->getDescription());
        $clone->setComment($source->getComment());
        $clone->setRequestedPriorDelivery($source->isRequestedPriorDelivery());
        $clone->setRequestedAtPurchaseOrder($source->isRequestedAtPurchaseOrder());
        $clone->setCompletionRate(0);

        return $clone;
    }
}
