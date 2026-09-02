<?php

declare(strict_types=1);

namespace App\Tests\Command\Sales\SalesForecast;

use App\Command\Sales\SalesForecast\SalesForecastDelinquentCommand;
use App\Doctrine\Voter\ActivityLogVoter;
use App\Entity\Sales\SalesForecast;
use App\Repository\Sales\SalesForecastRepository;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Timestampable\TimestampableListener;
use Prophecy\Argument\Token\CallbackToken;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SalesForecastDelinquentCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'tld:sales_forecast:delinquent';

    public function testCommandSetSalesForecastsDelinquent()
    {
        $salesForecasts = [];

        for ($i = 1; $i <= 10; ++$i) {
            $salesForecast = new SalesForecast();
            $salesForecast->setDelinquent(false);
            $salesForecasts[] = $salesForecast;
        }

        $repositoryMock = $this->getMockBuilder(SalesForecastRepository::class)->disableOriginalConstructor()->onlyMethods(['findNewDelinquent'])->getMock();
        $repositoryMock->expects($this->once())->method('findNewDelinquent')->willReturn($salesForecasts);

        $activityLogVoterProphecy = $this->prophesize(ActivityLogVoter::class);
        $activityLogVoterProphecy->disable()->shouldBeCalledTimes(1);

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(SalesForecast::class)->shouldBeCalledTimes(1)->willReturn($repositoryMock);
        $entityManagerProphecy->persist(new CallbackToken(static fn (SalesForecast $salesForecast) => $salesForecast->isDelinquent()))->shouldBeCalledTimes(10);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        $timestampableListener = new TimestampableListener();
        $eventManagerProphecy = $this->prophesize(EventManager::class);

        $eventManagerProphecy->getAllListeners()->shouldBeCalledTimes(1)->willReturn([[$timestampableListener]]);
        $eventManagerProphecy->removeEventListener($timestampableListener->getSubscribedEvents(), $timestampableListener)->shouldBeCalledTimes(1);

        $entityManagerProphecy->getEventManager()->shouldBeCalledTimes(1)->willReturn($eventManagerProphecy->reveal());

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new SalesForecastDelinquentCommand(
            $entityManagerProphecy->reveal(),
            $activityLogVoterProphecy->reveal()
        ));
        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
        ]);
    }
}
