<?php

declare(strict_types=1);

namespace App\Manager\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use Cake\Chronos\ChronosDate;

class FirstArticleQualificationDateReminderManager
{
    /**
     * @var string
     */
    final public const PLAN_DUE_DATE_SOON = 'PLAN_DUE_DATE_SOON';
    /**
     * @var string
     */
    final public const DUE_DATE_SOON = 'DUE_DATE_SOON';
    /**
     * @var string
     */
    final public const PLAN_DUE_DATE_PASSED = 'PLAN_DUE_DATE_PASSED';
    /**
     * @var string
     */
    final public const DUE_DATE_PASSED = 'DUE_DATE_PASSED';
    /**
     * @var string
     */
    final public const DELIVERABLES_DUE_DATE_PASSED = 'DELIVERABLES_DUE_DATE_PASSED';

    /**
     * @param FirstArticleQualification[] $firstArticleQualifications
     */
    public function handleFirstArticleQualifications(array $firstArticleQualifications)
    {
        $today = ChronosDate::now();
        $results = [];
        foreach ($firstArticleQualifications as $faq) {
            if (null === $faq->getPlanDefinitionCompletedAt()) {
                $dueDate = ChronosDate::createFromFormat('Y-m-d', $faq->getPlanDefinitionDueDate()->format('Y-m-d'));

                switch (true) {
                    case $today->equals($dueDate->subMonths(1)):
                        $results[self::PLAN_DUE_DATE_SOON][] = $faq;
                        break;
                    case $today->equals($dueDate->addDays(1)):
                        $results[self::PLAN_DUE_DATE_PASSED][] = $faq;
                }
            }

            if (null === $faq->getCompletedAt()) {
                $dueDate = ChronosDate::createFromFormat('Y-m-d', $faq->getDueDate()->format('Y-m-d'));

                switch (true) {
                    case $today->equals($dueDate->subMonths(1)):
                        $results[self::DUE_DATE_SOON][] = $faq;
                        break;
                    case $today->equals($dueDate->addDays(1)):
                        $results[self::DUE_DATE_PASSED][] = $faq;
                }

                if (null !== $faq->getDeliverablesDueDate()) {
                    $deliverablesDueDate = ChronosDate::createFromFormat('Y-m-d', $faq->getDeliverablesDueDate()->format('Y-m-d'));
                    if ($today->equals($deliverablesDueDate->addDays(1))) {
                        $results[self::DELIVERABLES_DUE_DATE_PASSED][] = $faq;
                    }
                }
            }
        }

        return $results;
    }
}
