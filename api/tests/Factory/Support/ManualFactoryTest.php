<?php

declare(strict_types=1);

namespace App\Tests\Factory\Support;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Support\Manual;
use App\Entity\Support\ManualDocument;
use App\Factory\Support\ManualDocumentFactory;
use App\Factory\Support\ManualFactory;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\Manuals;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ManualFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testFormattedSignalCodeForIonFilter()
    {
        $result = ManualFactory::getFormattedGroupSignaCode('chapters');
        $this->assertSame('CH0|CH1|CH2|CH3|CH5', $result);
        $result = ManualFactory::getFormattedGroupSignaCode('assembly_instructions');
        $this->assertSame('AIM|AIE|AIH|AIG', $result);
    }

    public function testCloneManual()
    {
        $dataProviderProphecy = $this->prophesize(CachedIONItemDataProvider::class);
        $manualDocumentFactoryProphecy = $this->prophesize(ManualDocumentFactory::class);
        $resourceMetadataFactoryProphecy = $this->prophesize(ResourceMetadataCollectionFactoryInterface::class);

        $manualFactory = new ManualFactory($dataProviderProphecy->reveal(), $manualDocumentFactoryProphecy->reveal(), $resourceMetadataFactoryProphecy->reveal());

        $originalManual = new Manual();
        $originalEquipmentRecord = new EquipmentRecord();
        $clonedEquipmentRecord = new EquipmentRecord();

        $originalEquipmentRecord->setType('A');
        $originalEquipmentRecord->setModel('B');
        $originalEquipmentRecord->setProjectNumber('1');
        $originalManual->equipmentRecord = $originalEquipmentRecord;
        $originalManual->description = '';

        $clonedEquipmentRecord->setType('Z');
        $clonedEquipmentRecord->setModel('Y');
        $clonedEquipmentRecord->setProjectNumber('2');

        $clonedManual = $manualFactory->cloneManual($originalManual, $clonedEquipmentRecord);

        $this->assertNotSame($clonedManual, $originalManual, 'The clone object should not be the same as the original');
        $this->assertNotSame($clonedManual->description, $originalManual->description, 'The clone object should not have the same description has the original');
        $this->assertMatchesRegularExpression(\sprintf('/Z, Y <br> \*\*\* Automatically generated from CBOM#2 on the %s \*\*\*/', date('Y-m-d')), $clonedManual->description);
    }

    /**
     * @dataProvider manualCreation
     */
    public function testCreateANewManualWithEmptyCustomizedBillOfMaterial(?string $language)
    {
        $location = new Location();
        $location->setErp(7);

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord
            ->setManufacturerLocation($location)
            ->setOptionsDescription('My option description')
            ->setType('A')
            ->setModel('B')
            ->setProjectNumber('1')
        ;

        $resourceMetadataFactoryProphecy = $this->prophesize(ResourceMetadataCollectionFactoryInterface::class);

        $operation = new Get(class: Manuals::class);
        $resourceMetadataFactoryProphecy->create(Manuals::class)->shouldBeCalledOnce()->willReturn(
            new ResourceMetadataCollection(Manuals::class, [new ApiResource(operations: [$operation])])
        );

        $date = (new \DateTime())->setTime(23, 59, 59);
        $dataProviderProphecy = $this->prophesize(CachedIONItemDataProvider::class);

        $dataProviderProphecy->provide(Argument::cetera())->shouldBeCalledTimes(2)->willReturn(null);

        $manualDocumentFactoryProphecy = $this->prophesize(ManualDocumentFactory::class);
        $manualDocumentFactoryProphecy->createCollectionFromCustomizedBillOfMaterials(Argument::cetera())->shouldNotBeCalled();

        $manualFactory = new ManualFactory($dataProviderProphecy->reveal(), $manualDocumentFactoryProphecy->reveal(), $resourceMetadataFactoryProphecy->reveal());

        $this->expectException(NotFoundHttpException::class);
        if (null === $language) {
            $manualFactory->create($equipmentRecord);
        } else {
            $manualFactory->create($equipmentRecord, $language);
        }
    }

    public function manualCreation()
    {
        yield 'Default language' => [null];
        yield 'French language' => ['FRENCH'];
    }

    public function testCreateANewManualWithCustomizedBillOfMaterial()
    {
        $location = new Location();
        $location->setErp(7);

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord
            ->setManufacturerLocation($location)
            ->setOptionsDescription('My option description')
            ->setType('A')
            ->setModel('B')
            ->setProjectNumber('1')
        ;

        $resourceMetadataFactoryProphecy = $this->prophesize(ResourceMetadataCollectionFactoryInterface::class);

        $operation = new Get(class: Manuals::class);
        $resourceMetadataFactoryProphecy->create(Manuals::class)->shouldBeCalledOnce()->willReturn(
            new ResourceMetadataCollection(Manuals::class, [new ApiResource(operations: [$operation])])
        );

        $dataProviderProphecy = $this->prophesize(CachedIONItemDataProvider::class);
        $dataProviderProphecy->provide(Argument::cetera())->shouldBeCalledTimes(2)->willReturn(new Manuals(), null);

        $manualDocumentFactoryProphecy = $this->prophesize(ManualDocumentFactory::class);
        $manualDocumentFactoryProphecy->createCollectionFromCustomizedBillOfMaterials(Argument::cetera())->shouldBeCalledOnce();

        $manualFactory = new ManualFactory($dataProviderProphecy->reveal(), $manualDocumentFactoryProphecy->reveal(), $resourceMetadataFactoryProphecy->reveal());
        $manual = $manualFactory->create($equipmentRecord);

        $this->assertSame('My option description', $manual->features, 'manual features should be the same as ER Options Description');
        $this->assertMatchesRegularExpression(\sprintf('/A, B <br> \*\*\* Automatically generated from CBOM#1 on the %s \*\*\*/', date('Y-m-d')), $manual->description, 'Manual description should the same as');
    }

    public function testCreateANewManualWithGreenTagDate()
    {
        $date = new \DateTime();

        $locationProphecy = $this->prophesize(Location::class);
        $locationProphecy->getErp()->willReturn(7);

        $equipmentRecordProphecy = $this->prophesize(EquipmentRecord::class);
        $equipmentRecordProphecy->getManufacturerLocation()->willReturn($locationProphecy->reveal());
        $equipmentRecordProphecy->getProjectNumber()->willReturn('123654');
        $equipmentRecordProphecy->getOptionsDescription()->willReturn('...');
        $equipmentRecordProphecy->getType()->willReturn('...');
        $equipmentRecordProphecy->getType()->willReturn('string');
        $equipmentRecordProphecy->getModel()->willReturn('string');
        $equipmentRecordProphecy->getGreenTagDate()->shouldBeCalledTimes(2)->willReturn($date);

        $documentList = new ArrayCollection();
        $documentList->add(new ManualDocument());

        $resourceMetadataFactoryProphecy = $this->prophesize(ResourceMetadataCollectionFactoryInterface::class);

        $operation = new Get(class: Manuals::class);
        $resourceMetadataFactoryProphecy->create(Manuals::class)->shouldBeCalledOnce()->willReturn(
            new ResourceMetadataCollection(Manuals::class, [new ApiResource(operations: [$operation])])
        );

        $dataProviderProphecy = $this->prophesize(CachedIONItemDataProvider::class);
        $dataProviderProphecy->provide(Argument::cetera())->shouldBeCalledTimes(2)->willReturn(new Manuals(), null);

        $manualFactory = new ManualFactory($dataProviderProphecy->reveal(), $this->prophesize(ManualDocumentFactory::class)->reveal(), $resourceMetadataFactoryProphecy->reveal());
        $manualFactory->create($equipmentRecordProphecy->reveal());
    }

    public function testCreateANewManualFromManualCustomizedBillOfMaterials()
    {
        $dataProviderProphecy = $this->prophesize(CachedIONItemDataProvider::class);
        $resourceMetadataFactoryProphecy = $this->prophesize(ResourceMetadataCollectionFactoryInterface::class);

        $manualDocumentFactoryProphecy = $this->prophesize(ManualDocumentFactory::class);
        $manualDocumentFactoryProphecy->createCollectionFromCustomizedBillOfMaterials(Argument::cetera())->shouldBeCalledOnce();

        $manualFactory = new ManualFactory($dataProviderProphecy->reveal(), $manualDocumentFactoryProphecy->reveal(), $resourceMetadataFactoryProphecy->reveal());
        $manualFactory->createFromManualCustomizedBillOfMaterials(new Manuals());
    }
}
