<?php

declare(strict_types=1);

namespace App\Tests\Javelo\Command;

use App\Entity\Activity\Log;
use App\Javelo\Command\NotificationCommand;
use App\Javelo\Notifier\Notifier;
use App\Repository\Common\LogRepository;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class NotificationCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testExecuteDaily(): void
    {
        $logRepository = $this->getMockBuilder(LogRepository::class)->disableOriginalConstructor()->onlyMethods(['findByPeriod'])->getMock();
        $notifier = $this->prophesize(Notifier::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $logs = [new Log(), new Log()];
        $logRepository->expects($this->once())->method('findByPeriod')->with('javelo', $this->callback(static fn ($date) => $date instanceof \DateTime), $this->callback(static fn ($date) => $date instanceof \DateTime))
            ->willReturn($logs);

        $notifier->sendMonitoring('Javelo Monitoring', $logs)->shouldBeCalledOnce();

        $command = new NotificationCommand(
            $logRepository,
            $notifier->reveal(),
            $logger->reveal()
        );

        $application = new Application();
        $application->addCommand($command);

        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'period' => 'monthly',
        ]);

        // Assertions
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Logs found:', $output);
    }

    public function testExecuteInvalidPeriod(): void
    {
        $logRepository = $this->getMockBuilder(LogRepository::class)->disableOriginalConstructor()->getMock();
        $notifier = $this->prophesize(Notifier::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $command = new NotificationCommand(
            $logRepository,
            $notifier->reveal(),
            $logger->reveal()
        );

        $application = new Application();
        $application->addCommand($command);

        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'period' => 'invalid_period',
        ]);

        // Assertions
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Invalid period specified.', $output);
    }

    public function testExecuteThrow(): void
    {
        $logRepository = $this->getMockBuilder(LogRepository::class)->disableOriginalConstructor()->onlyMethods(['findByPeriod'])->getMock();
        $notifier = $this->prophesize(Notifier::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $logs = [new Log(), new Log()];
        $logRepository->expects($this->once())->method('findByPeriod')->with('javelo', $this->callback(static fn ($date) => $date instanceof \DateTime), $this->callback(static fn ($date) => $date instanceof \DateTime))
            ->willReturn($logs);

        $notifier->sendMonitoring('Javelo Monitoring', $logs)->shouldBeCalledOnce()->willThrow(new \Exception());
        $today = (new \DateTime())->format('Y-m-d');
        $logger->error(
            'Something went wrong sending monitoring on {toDay}: {error}',
            ['error' => '', 'toDay' => $today]
        )->shouldBeCalledOnce();

        $command = new NotificationCommand(
            $logRepository,
            $notifier->reveal(),
            $logger->reveal()
        );

        $application = new Application();
        $application->addCommand($command);

        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'period' => 'monthly',
        ]);

        // Assertions
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Logs found:', $output);
    }
}
