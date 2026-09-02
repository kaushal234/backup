<?php

declare(strict_types=1);

namespace App\Tests\Link\Manager\Support;

use App\Entity\Directory\Location;
use App\Entity\EmissionRating;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Product;
use App\Http\Link\LinkChineseClient;
use App\Http\Link\LinkClient;
use App\Link\Manager\Support\EquipmentRecordManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class EquipmentRecordManagerTest extends TestCase
{
    use ProphecyTrait;

    public function testEquipmentIsCreatedWhenNeededOnWorldwideDomain()
    {
        $clientProphecy = $this->prophesize(LinkClient::class);
        $chineseClientProphecy = $this->prophesize(LinkChineseClient::class);
        $loggerProphecy = $this->prophesize(LoggerInterface::class);

        $equipmentRecord = (new EquipmentRecord())
            ->setSerialNumber('T13000')
            ->setManufacturerLocation((new Location())->setName('TLD MTL'))
            ->setEmissionRating((new EmissionRating())->setName('CN GB'))
            ->setProduct((new Product())->setName('TPX'))
        ;

        $linkEquipmentRecord = [
            'id' => 13,
            'plateNumber' => 'T13000',
            'astusId' => 'T13000',
            'identifier' => 'T13000',
            'energySource' => 'FUEL',
            'equipmentModel' => ['id' => 13, 'name' => 'TPX'],
            'organization' => ['id' => 13, 'name' => 'TLD MTL'],
        ];

        $locationList = ['TLD MTL' => ['id' => 13, 'name' => 'TLD MTL']];
        $productList = ['TPX' => ['id' => 13, 'name' => 'TPX']];
        $energySourceList = ['FUEL' => ['id' => 13, 'name' => 'FUEL']];

        $clientProphecy->getCollection(Location::class)->shouldBeCalledOnce()->willReturn($locationList);
        $clientProphecy->getCollection(Product::class)->shouldBeCalledOnce()->willReturn($productList);
        $clientProphecy->getCollection(EmissionRating::class)->shouldBeCalledOnce()->willReturn($energySourceList);
        $clientProphecy->getCollection(EquipmentRecord::class, 'plateNumber')->shouldBeCalledOnce()->willReturn(['T13000' => $linkEquipmentRecord]);

        //        $chineseClientProphecy->getCollection(Location::class)->shouldBeCalledOnce()->willReturn([]);
        //        $chineseClientProphecy->getCollection(Product::class)->shouldBeCalledOnce()->willReturn([]);
        //        $chineseClientProphecy->getCollection(EmissionRating::class)->shouldBeCalledOnce()->willReturn([]);
        //        $chineseClientProphecy->getCollection(EquipmentRecord::class, 'plateNumber')->shouldBeCalledOnce()->willReturn([]);

        $loggerProphecy->debug('Equipment T13000 has been skipped because no changes was found.')->shouldNotBeCalled();

        $clientProphecy->mutate($equipmentRecord, ['equipment_record_detail', 'product_list', 'location_public', 'emission_rating:detail'], [])->shouldBeCalledOnce();
        $loggerProphecy->debug('Equipment T13000 has been synchronized on Link')->shouldBeCalledOnce();

        $equipmentRecordManager = new EquipmentRecordManager($clientProphecy->reveal(), $chineseClientProphecy->reveal(), $loggerProphecy->reveal());
        $equipmentRecordManager->synchronizeEquipmentRecord([$equipmentRecord]);

        self::assertSame($equipmentRecord->getLinkId(), 13);
    }

    //    public function testEquipmentIsCreatedWhenNeededOnChineseDomain()
    //    {
    //        $clientProphecy = $this->prophesize(LinkClient::class);
    //        $chineseClientProphecy = $this->prophesize(LinkChineseClient::class);
    //        $loggerProphecy = $this->prophesize(LoggerInterface::class);
    //
    //        $equipmentRecord = (new EquipmentRecord())
    //            ->setSerialNumber('T13000')
    //            ->setManufacturerLocation((new Location())->setName('TLD WUX'))
    //            ->setEmissionRating((new EmissionRating())->setName('CN GB'))
    //            ->setProduct((new Product())->setName('TPX'))
    //        ;
    //
    //        $linkEquipmentRecord = [
    //            'id' => 13,
    //            'plateNumber' => 'T13000',
    //            'astusId' => 'T13000',
    //            'identifier' => 'T13000',
    //            'energySource' => 'FUEL',
    //            'equipmentModel' => ['id' => 13, 'name' => 'TPX'],
    //            'organization' => ['id' => 13, 'name' => 'TLD WUX'],
    //        ];
    //
    //        $locationList = ['TLD WUX' => ['id' => 13, 'name' => 'TLD WUX']];
    //        $productList = ['TPX' => ['id' => 13, 'name' => 'TPX']];
    //        $energySourceList = ['FUEL' => ['id' => 13, 'name' => 'FUEL']];
    //
    //        $chineseClientProphecy->getCollection(Location::class)->shouldBeCalledOnce()->willReturn($locationList);
    //        $chineseClientProphecy->getCollection(Product::class)->shouldBeCalledOnce()->willReturn($productList);
    //        $chineseClientProphecy->getCollection(EmissionRating::class)->shouldBeCalledOnce()->willReturn($energySourceList);
    //        $chineseClientProphecy->getCollection(EquipmentRecord::class, 'plateNumber')->shouldBeCalledOnce()->willReturn(['T13000' => $linkEquipmentRecord]);
    //
    //        $clientProphecy->getCollection(Location::class)->shouldBeCalledOnce()->willReturn([]);
    //        $clientProphecy->getCollection(Product::class)->shouldBeCalledOnce()->willReturn([]);
    //        $clientProphecy->getCollection(EmissionRating::class)->shouldBeCalledOnce()->willReturn([]);
    //        $clientProphecy->getCollection(EquipmentRecord::class, 'plateNumber')->shouldBeCalledOnce()->willReturn([]);
    //
    //        $loggerProphecy->debug('Equipment T13000 has been skipped because no changes was found.')->shouldNotBeCalled();
    //
    //        $chineseClientProphecy->mutate($equipmentRecord, ['equipment_record_detail', 'product_list', 'location_public', 'emission_rating:detail'], [])->shouldBeCalledOnce();
    //        $loggerProphecy->debug('Equipment T13000 has been synchronized on Link')->shouldBeCalledOnce();
    //
    //        $equipmentRecordManager = new EquipmentRecordManager($clientProphecy->reveal(), $chineseClientProphecy->reveal(), $loggerProphecy->reveal());
    //        $equipmentRecordManager->synchronizeEquipmentRecord([$equipmentRecord]);
    //
    //        self::assertSame($equipmentRecord->getLinkId(), 13);
    //    }

    //    public function testEquipmentIsSkipped()
    //    {
    //        $clientProphecy = $this->prophesize(LinkClient::class);
    //        $chineseClientProphecy = $this->prophesize(LinkChineseClient::class);
    //        $loggerProphecy = $this->prophesize(LoggerInterface::class);
    //
    //        $equipmentRecord = (new EquipmentRecord())
    //            ->setSerialNumber('T13000')
    //            ->setManufacturerLocation((new Location())->setName('TLD WUX'))
    //            ->setEmissionRating((new EmissionRating())->setName('CN GB'))
    //            ->setProduct((new Product())->setName('TPX'))
    //        ;
    //
    //        $linkEquipmentRecord = [
    //            'id' => 13,
    //            'plateNumber' => 'T13000',
    //            'astusId' => 'T13000',
    //            'identifier' => 'T13000',
    //            'energySource' => 'FUEL',
    //            'equipmentModel' => ['id' => 13, 'name' => 'TPX'],
    //            'organization' => ['id' => 13, 'name' => 'TLD_WUX'],
    //        ];
    //
    //        $locationList = ['TLD MTL' => ['id' => 13, 'name' => 'TLD_MTL']];
    //        $productList = ['TPX' => ['id' => 13, 'name' => 'TPX']];
    //        $energySourceList = ['FUEL' => ['id' => 13, 'name' => 'FUEL']];

    //        $chineseClientProphecy->getCollection(Location::class)->shouldBeCalledOnce()->willReturn($locationList);
    //        $chineseClientProphecy->getCollection(Product::class)->shouldBeCalledOnce()->willReturn($productList);
    //        $chineseClientProphecy->getCollection(EmissionRating::class)->shouldBeCalledOnce()->willReturn($energySourceList);
    //        $chineseClientProphecy->getCollection(EquipmentRecord::class, 'plateNumber')->shouldBeCalledOnce()->willReturn(['T13000' => $linkEquipmentRecord]);
    //
    //        $clientProphecy->getCollection(Location::class)->shouldBeCalledOnce()->willReturn([]);
    //        $clientProphecy->getCollection(Product::class)->shouldBeCalledOnce()->willReturn([]);
    //        $clientProphecy->getCollection(EmissionRating::class)->shouldBeCalledOnce()->willReturn([]);
    //        $clientProphecy->getCollection(EquipmentRecord::class, 'plateNumber')->shouldBeCalledOnce()->willReturn([]);
    //
    //        $loggerProphecy->debug('Equipment T13000 has been skipped because no changes was found.')->shouldBeCalledOnce();

    //        $chineseClientProphecy->mutate($equipmentRecord, ['equipment_record_detail', 'product_list', 'location_public', 'emission_rating:detail'])->shouldNotBeCalled();
    //        $loggerProphecy->debug('Equipment T13000 has been synchronized on Link')->shouldNotBeCalled();
    //
    //        $equipmentRecordManager = new EquipmentRecordManager($clientProphecy->reveal(), $chineseClientProphecy->reveal(), $loggerProphecy->reveal());
    //        $equipmentRecordManager->synchronizeEquipmentRecord([$equipmentRecord]);
    //
    //        self::assertSame($equipmentRecord->getLinkId(), 13);
    //    }

    //    public function testEquipmentIsArchived()
    //    {
    //        $clientProphecy = $this->prophesize(LinkClient::class);
    //        $chineseClientProphecy = $this->prophesize(LinkChineseClient::class);
    //        $loggerProphecy = $this->prophesize(LoggerInterface::class);
    //
    //        $equipmentRecord = (new EquipmentRecord())
    //            ->setSerialNumber('T13000')
    //            ->setManufacturerLocation((new Location())->setName('TLD MTL'))
    //            ->setEmissionRating((new EmissionRating())->setName('CN GB'))
    //            ->setProduct((new Product())->setName('TPX'))
    //        ;
    //
    //        $linkEquipmentRecord = [
    //            'id' => 13,
    //            'plateNumber' => 'T13000',
    //            'astusId' => 'T13000',
    //            'identifier' => 'T13000',
    //            'energySource' => 'FUEL',
    //            'equipmentModel' => ['id' => 13, 'name' => 'TPX'],
    //            'organization' => ['id' => 13, 'name' => 'TLD_MTL'],
    //        ];
    //
    //        $locationList = ['TLD MTL' => ['id' => 13, 'name' => 'TLD_MTL']];
    //        $productList = ['TPX' => ['id' => 13, 'name' => 'TPX']];
    //        $energySourceList = ['FUEL' => ['id' => 13, 'name' => 'FUEL']];
    //
    //        $chineseClientProphecy->getCollection(Location::class)->shouldBeCalledOnce()->willReturn($locationList);
    //        $chineseClientProphecy->getCollection(Product::class)->shouldBeCalledOnce()->willReturn($productList);
    //        $chineseClientProphecy->getCollection(EmissionRating::class)->shouldBeCalledOnce()->willReturn($energySourceList);
    //        $chineseClientProphecy->getCollection(EquipmentRecord::class, 'plateNumber')->shouldBeCalledOnce()->willReturn(['T13000' => $linkEquipmentRecord]);
    //
    //        $clientProphecy->getCollection(Location::class)->shouldBeCalledOnce()->willReturn([]);
    //        $clientProphecy->getCollection(Product::class)->shouldBeCalledOnce()->willReturn([]);
    //        $clientProphecy->getCollection(EmissionRating::class)->shouldBeCalledOnce()->willReturn([]);
    //        $clientProphecy->getCollection(EquipmentRecord::class, 'plateNumber')->shouldBeCalledOnce()->willReturn(['T13000' => $linkEquipmentRecord]);
    //
    //        $chineseClientProphecy->archive($equipmentRecord);
    //
    //        $loggerProphecy->debug('Equipment T13000 archived into Link.')->shouldBeCalledOnce();
    //
    //        $loggerProphecy->debug('Equipment T13000 has been skipped because no changes was found.')->shouldBeCalledOnce();
    //
    //        $chineseClientProphecy->mutate($equipmentRecord, ['equipment_record_detail', 'product_list', 'location_public', 'emission_rating:detail'])->shouldNotBeCalled();
    //        $loggerProphecy->debug('Equipment T13000 has been synchronized on Link')->shouldNotBeCalled();
    //
    //        $equipmentRecordManager = new EquipmentRecordManager($clientProphecy->reveal(), $chineseClientProphecy->reveal(), $loggerProphecy->reveal());
    //        $equipmentRecordManager->synchronizeEquipmentRecord([$equipmentRecord]);
    //
    //        self::assertSame($equipmentRecord->getLinkId(), 13);
    //    }
}
