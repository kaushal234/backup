<?php

declare(strict_types=1);

namespace App\Tests\Command\Sales\Demo;

use App\Command\Sales\Demo\DemoDelinquentCommand;
use App\Entity\Sales\Demo;
use App\Notifier\Sales\Demo\DemoNotifier;
use App\Repository\Sales\DemoRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DemoDelinquentCommandTest extends KernelTestCase
{
    /**
     * @var string
     */
    final public const COMMAND = 'api:sales:delinquent_demos';

    public function testExecute()
    {
        /** @var EntityManagerInterface|MockObject $entityManagerMock */
        $entityManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var DemoRepository|MockObject $demoRepositoryMock */
        $demoRepositoryMock = $this->getMockBuilder(DemoRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var DemoNotifier|MockObject $demoNotifier */
        $demoNotifier = $this->getMockBuilder(DemoNotifier::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Demo|MockObject $demoDeMinuitDix */
        $demoDeMinuitDix = $this->getMockBuilder(Demo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $demoRepositoryMock
            ->expects(self::once())
            ->method('findExpiredDemos')
            ->willReturn([$demoDeMinuitDix]);

        $demoDeMinuitDix
            ->expects(self::once())
            ->method('setDelinquent');

        $entityManagerMock
            ->expects(self::once())
            ->method('persist')
            ->with($demoDeMinuitDix);

        $entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $demoNotifier
            ->expects(self::once())
            ->method('sendDelinquentEmail')
            ->with($demoDeMinuitDix, 'revised end date');

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new DemoDelinquentCommand($entityManagerMock, $demoRepositoryMock, $demoNotifier));

        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
        ]);
    }
}
