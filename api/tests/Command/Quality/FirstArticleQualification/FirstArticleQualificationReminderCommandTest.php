<?php

declare(strict_types=1);

namespace App\Tests\Command\Quality\FirstArticleQualification;

use App\Command\Quality\FirstArticleQualification\FirstArticleQualificationReminderCommand;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Manager\Quality\FirstArticleQualification\FirstArticleQualificationDateReminderManager;
use App\Notifier\Quality\FirstArticleQualification\FirstArticleQualificationNotifier;
use App\Repository\Quality\FirstArticleQualification\FirstArticleQualificationRepository;
use Cake\Chronos\Chronos;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class FirstArticleQualificationReminderCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'tld:faq:reminder';

    public function testExecuteWithNoFAQToRemind()
    {
        $faqNotifier = $this->prophesize(FirstArticleQualificationNotifier::class);
        $faqRepository = $this->getMockBuilder(FirstArticleQualificationRepository::class)->disableOriginalConstructor()->onlyMethods(['findFAQForReminderDateCommand'])->getMock();
        $faqDateReminderManagerProphecy = $this->prophesize(FirstArticleQualificationDateReminderManager::class);

        $faqNotifier->sendReminderStatus(Argument::any())->shouldNotBeCalled();
        $faqRepository->expects($this->once())->method('findFAQForReminderDateCommand')->willReturn([]);

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new FirstArticleQualificationReminderCommand(
            $faqRepository,
            $faqDateReminderManagerProphecy->reveal(),
            $faqNotifier->reveal()
        ));
        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
        ]);
    }

    public function testExecuteWithFAQToRemind()
    {
        $faqNotifier = $this->prophesize(FirstArticleQualificationNotifier::class);
        $faqRepository = $this->getMockBuilder(FirstArticleQualificationRepository::class)->disableOriginalConstructor()->onlyMethods(['findFAQForReminderDateCommand'])->getMock();

        Chronos::setTestNow('2018-02-28');

        $faqPlanDueDateSoon = (new FirstArticleQualification())->setPlanDefinitionDueDate(new \DateTime('2018-03-30'))->setPlanDefinitionCompletedAt(null)->setCompletedAt(new \DateTime());
        $faqDueDateSoon = (new FirstArticleQualification())->setDueDate(new \DateTime('2018-03-30'))->setCompletedAt(null)->setPlanDefinitionCompletedAt(new \DateTime());
        $faqPlanDueDatePassed = (new FirstArticleQualification())->setPlanDefinitionDueDate(new \DateTime('2018-02-27'))->setPlanDefinitionCompletedAt(null)->setCompletedAt(new \DateTime());
        $faqDueDatePassed = (new FirstArticleQualification())->setDueDate(new \DateTime('2018-02-27'))->setCompletedAt(null)->setPlanDefinitionCompletedAt(new \DateTime());
        $faqDeliverablesDueDatePassed = (new FirstArticleQualification())->setDueDate(new \DateTime('2020-01-01'))->setDeliverablesDueDate(new \DateTime('2018-02-27'))->setCompletedAt(null)->setPlanDefinitionCompletedAt(new \DateTime());

        $faqRepository
            ->expects($this->once())
            ->method('findFAQForReminderDateCommand')
            ->willReturn(array_fill(0, 4, new FirstArticleQualification()))
            ->willReturn([$faqDueDatePassed, $faqDueDateSoon, $faqPlanDueDatePassed, $faqPlanDueDateSoon, $faqDeliverablesDueDatePassed])
        ;

        $faqsForNotifications = [
            FirstArticleQualificationDateReminderManager::PLAN_DUE_DATE_SOON => [$faqPlanDueDateSoon],
            FirstArticleQualificationDateReminderManager::DUE_DATE_SOON => [$faqDueDateSoon],
            FirstArticleQualificationDateReminderManager::PLAN_DUE_DATE_PASSED => [$faqPlanDueDatePassed],
            FirstArticleQualificationDateReminderManager::DUE_DATE_PASSED => [$faqDueDatePassed],
            FirstArticleQualificationDateReminderManager::DELIVERABLES_DUE_DATE_PASSED => [$faqDeliverablesDueDatePassed],
        ];

        $faqNotifier->sendReminderStatus($faqsForNotifications)->shouldBeCalledOnce();

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new FirstArticleQualificationReminderCommand(
            $faqRepository,
            // setting a real FirstArticleQualificationDateReminderManager to test that it returns what we want
            new FirstArticleQualificationDateReminderManager(),
            $faqNotifier->reveal()
        ));
        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
        ]);
    }
}
