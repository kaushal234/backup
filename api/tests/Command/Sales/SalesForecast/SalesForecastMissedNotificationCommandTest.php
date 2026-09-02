<?php

declare(strict_types=1);

namespace App\Tests\Command\Sales\SalesForecast;

use App\Command\Sales\SalesForecast\SalesForecastMissedNotificationCommand;
use App\Doctrine\Voter\ActivityLogVoter;
use App\Entity\Sales\SalesForecast;
use App\Notifier\Sales\SalesForecast\SalesForecastNotifier;
use App\Repository\Sales\SalesForecastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Prophecy\Argument\Token\CallbackToken;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SalesForecastMissedNotificationCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'tld:sales_forecast:notifications';

    public function testCommandFetchNonNotifiedClosedSFRAndSendEmails()
    {
        $salesForecasts = [];

        for ($i = 1; $i <= 10; ++$i) {
            $salesForecast = new SalesForecast();
            $salesForecasts[] = $salesForecast;
        }

        $repositoryMock = $this->getMockBuilder(SalesForecastRepository::class)->disableOriginalConstructor()->onlyMethods(['findClosedAndNotNotified'])->getMock();
        $repositoryMock->expects($this->once())->method('findClosedAndNotNotified')->with($this->callback(static fn ($date) => $date instanceof \DateTime), $this->callback(static fn ($date) => $date instanceof \DateTime))->willReturn($salesForecasts);

        $activityLogVoterProphecy = $this->prophesize(ActivityLogVoter::class);
        $activityLogVoterProphecy->disable()->shouldBeCalledTimes(1);

        $notifierProphecy = $this->prophesize(SalesForecastNotifier::class);
        $notifierProphecy->sendEmail(Argument::type(SalesForecast::class), 'sfr.subject.closure', 'sales_forecast_closure.html.twig')->shouldBeCalledTimes(10);

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(SalesForecast::class)->shouldBeCalledTimes(1)->willReturn($repositoryMock);
        $entityManagerProphecy->persist(new CallbackToken(static fn (SalesForecast $salesForecast) => null !== $salesForecast->getClosureNotificationSentAt()))->shouldBeCalledTimes(10);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new SalesForecastMissedNotificationCommand(
            $entityManagerProphecy->reveal(),
            $activityLogVoterProphecy->reveal(),
            $notifierProphecy->reveal()
        ));
        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
        ]);
    }
}
