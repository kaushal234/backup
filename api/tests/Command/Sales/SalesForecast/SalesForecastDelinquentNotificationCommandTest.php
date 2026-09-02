<?php

declare(strict_types=1);

namespace App\Tests\Command\Sales\SalesForecast;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Command\Sales\SalesForecast\SalesForecastDelinquentNotificationCommand;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Sales\SalesForecast;
use App\Notifier\Sales\SalesForecast\SalesForecastNotifier;
use App\Repository\Sales\SalesForecastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\FilterCollection;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SalesForecastDelinquentNotificationCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'tld:sales_forecast:delinquent-notifications';

    public function testCommandNotifiesSalesForecastsDelinquent()
    {
        $asmOne = new People();
        $asmTwo = new People();
        $location = new Location();

        $sfrOne = (new SalesForecast())->setAsm($asmOne)->setSso($location);
        $sfrTwo = (new SalesForecast())->setAsm($asmTwo)->setSso($location);

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $notifierProphecy = $this->prophesize(SalesForecastNotifier::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $repositoryMock = $this->getMockBuilder(SalesForecastRepository::class)->disableOriginalConstructor()->onlyMethods(['findOpenAndDeliquent'])->getMock();

        $repositoryMock->expects($this->once())->method('findOpenAndDeliquent')->with()->willReturn([$sfrOne, $sfrTwo]);
        $iriConverterProphecy->getIriFromResource($asmOne)->shouldBeCalledOnce()->willReturn('/foo/1');
        $iriConverterProphecy->getIriFromResource($asmTwo)->shouldBeCalledOnce()->willReturn('/foo/2');
        $iriConverterProphecy->getIriFromResource($location)->shouldBeCalledTimes(2)->willReturn('/bar/2');

        $iriConverterProphecy->getResourceFromIri('/foo/1')->shouldBeCalledOnce()->willReturn($asmOne);
        $iriConverterProphecy->getResourceFromIri('/foo/2')->shouldBeCalledOnce()->willReturn($asmTwo);
        $iriConverterProphecy->getResourceFromIri('/bar/2')->shouldBeCalledTimes(2)->willReturn($location);

        $notifierProphecy->sendDelinquent([$sfrOne], $asmOne, $location)->shouldBeCalledTimes(1);
        $notifierProphecy->sendDelinquent([$sfrTwo], $asmTwo, $location)->shouldBeCalledTimes(1);

        $filterCollectionProphecy = $this->prophesize(FilterCollection::class);
        $filterCollectionProphecy->disable('softdeleteable')->shouldBeCalledTimes(1);
        $entityManagerProphecy->getFilters()->shouldBeCalledTimes(1)->willReturn($filterCollectionProphecy->reveal());
        $entityManagerProphecy->getRepository(SalesForecast::class)->shouldBeCalledTimes(1)->willReturn($repositoryMock);

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new SalesForecastDelinquentNotificationCommand(
            $entityManagerProphecy->reveal(),
            $notifierProphecy->reveal(),
            $iriConverterProphecy->reveal()
        ));
        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
        ]);
    }
}
