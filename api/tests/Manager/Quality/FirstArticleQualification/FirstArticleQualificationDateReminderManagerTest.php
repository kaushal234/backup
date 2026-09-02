<?php

declare(strict_types=1);

namespace App\Tests\Manager\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Manager\Quality\FirstArticleQualification\FirstArticleQualificationDateReminderManager;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;

class FirstArticleQualificationDateReminderManagerTest extends TestCase
{
    public function testHandleFirstArticleQualifications()
    {
        Chronos::setTestNow('2018-02-28');

        $faqPlanDueDateSoon = (new FirstArticleQualification())->setPlanDefinitionDueDate(new \DateTime('2018-03-30'))->setPlanDefinitionCompletedAt(null)->setCompletedAt(new \DateTime());
        $faqDueDateSoon = (new FirstArticleQualification())->setDueDate(new \DateTime('2018-03-30'))->setCompletedAt(null)->setPlanDefinitionCompletedAt(new \DateTime());
        $faqPlanDueDatePassed = (new FirstArticleQualification())->setPlanDefinitionDueDate(new \DateTime('2018-02-27'))->setPlanDefinitionCompletedAt(null)->setCompletedAt(new \DateTime());
        $faqDueDatePassed = (new FirstArticleQualification())->setDueDate(new \DateTime('2018-02-27'))->setCompletedAt(null)->setPlanDefinitionCompletedAt(new \DateTime());
        $faqDeliverablesDueDatePassed = (new FirstArticleQualification())->setDueDate(new \DateTime('2020-01-01'))->setDeliverablesDueDate(new \DateTime('2018-02-27'))->setCompletedAt(null)->setPlanDefinitionCompletedAt(new \DateTime());
        $faqDeliverablesDueDateNotSet = (new FirstArticleQualification())->setDueDate(new \DateTime('2020-01-01'))->setCompletedAt(null)->setPlanDefinitionCompletedAt(new \DateTime());

        $manager = new FirstArticleQualificationDateReminderManager();

        $results = $manager->handleFirstArticleQualifications([
            $faqPlanDueDateSoon,
            $faqDueDateSoon,
            $faqPlanDueDatePassed,
            $faqDueDatePassed,
            $faqDeliverablesDueDatePassed,
            $faqDeliverablesDueDateNotSet,
        ]);

        $this->assertSame([
            FirstArticleQualificationDateReminderManager::PLAN_DUE_DATE_SOON => [$faqPlanDueDateSoon],
            FirstArticleQualificationDateReminderManager::DUE_DATE_SOON => [$faqDueDateSoon],
            FirstArticleQualificationDateReminderManager::PLAN_DUE_DATE_PASSED => [$faqPlanDueDatePassed],
            FirstArticleQualificationDateReminderManager::DUE_DATE_PASSED => [$faqDueDatePassed],
            FirstArticleQualificationDateReminderManager::DELIVERABLES_DUE_DATE_PASSED => [$faqDeliverablesDueDatePassed],
        ], $results);
    }
}
