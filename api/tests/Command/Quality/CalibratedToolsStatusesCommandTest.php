<?php

declare(strict_types=1);

namespace App\Tests\Command\Quality;

use App\Command\Quality\CalibratedTools\CalibratedToolsStatusesCommand;
use App\Entity\Directory\People;
use App\Entity\Quality\CalibratedTools\CalibrationLog;
use App\Entity\Quality\CalibratedTools\Tool;
use App\Entity\Quality\LocationArea;
use App\Notifier\Quality\CalibratedToolNotifier;
use App\Repository\Quality\ToolRepository;
use App\Workflow\WorkflowStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CalibratedToolsStatusesCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'tld:notifications:calibrated_tools';

    public function testExecute()
    {
        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $notifierProphecy = $this->prophesize(CalibratedToolNotifier::class);
        $repositoryMock = $this->getMockBuilder(ToolRepository::class)->disableOriginalConstructor()->onlyMethods(['getActiveAndCalibrationDueSoonTools'])->getMock();

        /** @var People $supervisorOne */
        $supervisorOne = (new People())->setEmail('superman@tld.fr');
        /** @var People $supervisorTwo */
        $supervisorTwo = (new People())->setEmail('batman@tld.fr');
        $toolOne = (new Tool())->setCalibrationInterval(5)->addCalibrationLog(new CalibrationLog())->setCalibrationNotice(5)->setNextCalibrationDate(new \DateTime('- 1 month'))->setStatus(Tool::ACTIVE)->setLocationArea((new LocationArea())->setSupervisor($supervisorOne));
        $toolTwo = (new Tool())->addCalibrationLog(new CalibrationLog())->setCalibrationNotice(5)->setNextCalibrationDate(new \DateTime('- 1 month'))->setStatus(Tool::CALIBRATION_DUE_SOON)->setLocationArea((new LocationArea())->setSupervisor($supervisorOne));
        $toolThree = (new Tool())->setCalibrationInterval(5)->addCalibrationLog(new CalibrationLog())->setCalibrationNotice(5)->setNextCalibrationDate(new \DateTime('- 1 month'))->setStatus(Tool::ACTIVE)->setLocationArea((new LocationArea())->setSupervisor($supervisorTwo));
        $toolNotNotified = (new Tool())->setStatus(Tool::ACTIVE);

        foreach ([$toolOne, $toolTwo, $toolThree] as $key => $tool) {
            $refl = new \ReflectionClass($tool);
            $reflectionProperty = $refl->getProperty('id');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($tool, $key + 1);
        }

        $entityManagerProphecy->getRepository(Tool::class)->shouldBeCalledOnce()->willReturn($repositoryMock);
        $repositoryMock->expects($this->once())->method('getActiveAndCalibrationDueSoonTools')->willReturn([$toolOne, $toolTwo, $toolThree, $toolNotNotified]);

        $workflowStatusUpdaterProphecy->applyStatus($toolNotNotified, Tool::OUT_OF_SERVICE)->shouldBeCalledOnce();
        $workflowStatusUpdaterProphecy->applyStatus($toolOne, Tool::CALIBRATION_DUE_SOON)->shouldBeCalledOnce();
        $workflowStatusUpdaterProphecy->applyStatus($toolThree, Tool::CALIBRATION_DUE_SOON)->shouldBeCalledOnce();
        $workflowStatusUpdaterProphecy->applyStatus($toolTwo, Tool::EXPIRED)->shouldBeCalledOnce();

        $entityManagerProphecy->persist($toolOne)->shouldBeCalledOnce();
        $entityManagerProphecy->persist($toolTwo)->shouldBeCalledOnce();
        $entityManagerProphecy->persist($toolThree)->shouldBeCalledOnce();
        $entityManagerProphecy->persist($toolNotNotified)->shouldBeCalledOnce();

        $notifierProphecy->sendEmail([$toolOne, $toolTwo], 'superman@tld.fr')->shouldBeCalledOnce();
        $notifierProphecy->sendEmail([$toolThree], 'batman@tld.fr')->shouldBeCalledOnce();

        $entityManagerProphecy->flush()->shouldBeCalledOnce();

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new CalibratedToolsStatusesCommand($workflowStatusUpdaterProphecy->reveal(), $entityManagerProphecy->reveal(), $notifierProphecy->reveal()));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => self::COMMAND,
        ]);
    }
}
