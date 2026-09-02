<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Finance;

use App\Dto\Finance\ManufacturingMarginBatch;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Finance\Currency;
use App\Entity\Finance\ManufacturingMargin;
use App\EventListener\Finance\ManufacturingMargin\ManufacturingMarginBatchUploadListener;
use App\Notifier\Finance\ManufacturingMarginNotifier;
use App\Repository\Directory\LocationRepository;
use LegacyBundle\Manager\ManufacturingMarginManager;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ManufacturingMarginBatchUploadListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testReportIsBuildAndSent()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $manufacturingMarginManagerProphecy = $this->prophesize(ManufacturingMarginManager::class);
        $locationRepositoryMock = $this->getMockBuilder(LocationRepository::class)->disableOriginalConstructor()->onlyMethods(['findOneBy'])->getMock();
        $notifierProphecy = $this->prophesize(ManufacturingMarginNotifier::class);
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $exportedAt = new \DateTime('2019-01');
        $manufacturingMargin = (new ManufacturingMargin())
            ->setActualOtherDirectCost(149)
            ->setStandardOtherDirectCost(150)
            ->setActualOtherMaterialCost(0)
            ->setStandardOtherMaterialCost(0)
            ->setActualMaterialCost(168)
            ->setStandardMaterialCost(170)
            ->setActualLabourCost(180)
            ->setStandardLabourCost(165)
            ->setActualHours(500)
            ->setStandardHours(200)
            ->setOptionConfigurationParameterHours(10)
            ->setFactoryRevenue(100_050)
            ->setEquipmentRecord((new EquipmentRecord())->setLegacyId(6_969)->setSerialNumber('TEST'))
            ->setCurrency((new Currency())->setName('EUR'))
            ->setExportedAt($exportedAt);

        $linkedSol = [
            'est_dir_margin_per' => 20,
            'sol_id' => 21_452,
            'sso_fullname' => 'TLD EUR',
            'erp_fullname' => 'TLD MTL',
            'buyer' => 'BUYER TEST',
            'model' => 'MODEL TEST',
        ];

        $manufacturingMarginBatch = (new ManufacturingMarginBatch())->addMargin($manufacturingMargin);

        $serviceLocatorProphecy->get(ManufacturingMarginManager::class)->shouldBeCalledTimes(1)->willReturn($manufacturingMarginManagerProphecy->reveal());
        $manufacturingMarginManagerProphecy->getSalesOrderLineInformation($manufacturingMargin)->shouldBeCalledTimes(1)->willReturn($linkedSol);

        $serviceLocatorProphecy->get(LocationRepository::class)->shouldBeCalledTimes(1)->willReturn($locationRepositoryMock);
        $locationRepositoryMock->expects($this->once())->method('findOneBy')->with(['name' => 'TLD MTL'])->willReturn($factory = new Location());

        $serviceLocatorProphecy->get(ManufacturingMarginNotifier::class)->shouldBeCalledTimes(1)->willReturn($notifierProphecy->reveal());
        $notifierProphecy->sendReport(Argument::type('array'), $factory, $exportedAt)->shouldBeCalledOnce();

        $listener = new ManufacturingMarginBatchUploadListener($serviceLocatorProphecy->reveal());

        $listener->onPostUpload(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $manufacturingMarginBatch));
    }
}
