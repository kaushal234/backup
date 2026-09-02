<?php

declare(strict_types=1);

namespace App\Tests\Command\Sales\SalesForecast;

use App\Command\Sales\SalesForecast\SalesForecastSnapshotCommand;
use App\Entity\Sales\SalesForecast;
use App\Entity\Sales\SalesForecastSnapshot;
use App\Factory\SalesForecastSnapshotFactory;
use App\Repository\Sales\SalesForecastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument\Token\CallbackToken;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SalesForecastSnapshotCommandTest extends KernelTestCase
{
    use ProphecyTrait;
    /**
     * @var string
     */
    final public const COMMAND = 'tld:sales_forecast:snapshot';

    public function testSalesForecastSnapshotCommand()
    {
        $salesForecasts = [new SalesForecast()];

        $repositoryMock = $this->getMockBuilder(SalesForecastRepository::class)->disableOriginalConstructor()->onlyMethods(['findOpen'])->getMock();
        $repositoryMock->expects($this->once())->method('findOpen')->willReturn($salesForecasts);

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(SalesForecast::class)->shouldBeCalledTimes(1)->willReturn($repositoryMock);
        $entityManagerProphecy->persist(new CallbackToken(static fn ($snapshot) => $snapshot instanceof SalesForecastSnapshot))->shouldBeCalledTimes(1);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        $factoryProphecy = $this->prophesize(SalesForecastSnapshotFactory::class);
        $factoryProphecy->createSnapshot($salesForecasts[0])->shouldBeCalledTimes(1);

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new SalesForecastSnapshotCommand($entityManagerProphecy->reveal(), $factoryProphecy->reveal()));

        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
        ]);
    }
}
