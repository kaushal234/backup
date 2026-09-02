<?php

declare(strict_types=1);

namespace App\Tests\Factory\Support;

use App\Entity\Support\Manual;
use App\Entity\Support\ManualDocument;
use App\Entity\Support\ManualDocumentCategory;
use App\Entity\Support\ManualPart;
use App\Factory\Support\ManualDocumentFactory;
use App\Factory\Support\ManualPartFactory;
use App\Factory\VaultFileFactory;
use App\FileSystem\VaultPartFileProvider;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterialItem;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\ManualPart as ManualPartLn;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\Manuals;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterialsItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\File\File;

class ManualDocumentFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateAlistOfManualDocument()
    {
        $manual = new Manual();
        $manualPart = new ManualPart();
        $manualPartsList = new ArrayCollection();
        $manualPartsList->add($manualPart);

        $customizedBillOfMaterialsItem = new ManualPartLn();
        $customizedBillOfMaterialsItem->partNumber = 'A';
        $customizedBillOfMaterialsItem->itemDescription = 'Eng desc';
        $customizedBillOfMaterialsItem->itemOtherDescription = 'Other';
        $customizedBillOfMaterialsItem->quantity = 2.3;
        $customizedBillOfMaterialsItem->signalCodeDescription = 'LU';
        $customizedBillOfMaterialsItem->engineeringSignalCode = 'LU';
        $customizedBillOfMaterialsItem->engineeringRevision = 'A';

        $customizedBillOfMaterialsItem2 = new ManualPartLn();
        $customizedBillOfMaterialsItem2->partNumber = 'A';
        $customizedBillOfMaterialsItem2->signalCodeDescription = 'LU';

        $customizedBillOfMaterialsItem3 = new ManualPartLn();
        $customizedBillOfMaterialsItem3->partNumber = 'B';
        $customizedBillOfMaterialsItem3->itemDescription = 'Eng desc';
        $customizedBillOfMaterialsItem3->itemOtherDescription = 'Other';
        $customizedBillOfMaterialsItem3->quantity = 2.3;
        $customizedBillOfMaterialsItem3->signalCodeDescription = 'LU';
        $customizedBillOfMaterialsItem3->engineeringSignalCode = 'LU';
        $customizedBillOfMaterialsItem3->engineeringRevision = 'B';

        $customizedBillOfMaterials = new Manuals();
        $customizedBillOfMaterials->addItem($customizedBillOfMaterialsItem);
        $customizedBillOfMaterials->addItem($customizedBillOfMaterialsItem2);
        $customizedBillOfMaterials->addItem($customizedBillOfMaterialsItem3);

        $manualDocumentRepositoryMock = $this->createMock(EntityRepository::class);
        $manualDocumentRepositoryMock->expects($this->once())->method('findOneBy')->willReturn(new ManualDocumentCategory());

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(ManualDocumentCategory::class)->shouldBeCalledOnce()->willReturn($manualDocumentRepositoryMock);

        $manualPartFactoryProphecy = $this->prophesize(ManualPartFactory::class);
        $manualPartFactoryProphecy->createCollectionFromCustomizedBillOfMaterialsItem(Argument::cetera())->shouldBeCalledTimes(2);

        $vaultFileFactoryProphecy = $this->prophesize(VaultFileFactory::class);
        $vaultPartFileProviderProphecy = $this->prophesize(VaultPartFileProvider::class);

        $manualDocumentFactory = new ManualDocumentFactory($entityManagerProphecy->reveal(), $vaultFileFactoryProphecy->reveal(), $manualPartFactoryProphecy->reveal(), $vaultPartFileProviderProphecy->reveal());
        $manualDocumentFactory->createCollectionFromCustomizedBillOfMaterials($customizedBillOfMaterials, $manual, 'chapters', true);

        $this->assertCount(2, $manual->getDocuments());
        $this->assertInstanceOf(ManualDocument::class, $manual->getDocuments()->first());
        $this->assertSame('A', $manual->getDocuments()->first()->factoryNumber);
        $this->assertSame('A', $manual->getDocuments()->first()->revision);
        $this->assertInstanceOf(ManualDocumentCategory::class, $manual->getDocuments()->first()->category);
        $this->assertSame('Eng desc', $manual->getDocuments()->first()->description);
        $this->assertSame('Other', $manual->getDocuments()->first()->otherDescription);
        $this->assertSame(2.3, $manual->getDocuments()->first()->quantity);
        $this->assertSame('MANUAL:SECTION', $manual->getDocuments()->first()->type);
    }

    public function testCreateAManualDocumentFromBillOfMaterials()
    {
        $manual = new Manual();
        $manualPart = new ManualPart();
        $manualPartsList = new ArrayCollection();
        $manualPartsList->add($manualPart);

        $customizedBillOfMaterialsItem2 = new CustomizedBillOfMaterialsItem();
        $customizedBillOfMaterialsItem2->partNumber = 'A';
        $customizedBillOfMaterialsItem2->signalCodeDescription = 'LU';

        $customizedBillOfMaterialsItem3 = new CustomizedBillOfMaterialsItem();
        $customizedBillOfMaterialsItem3->partNumber = 'B';
        $customizedBillOfMaterialsItem3->itemDescription = 'Eng desc';
        $customizedBillOfMaterialsItem3->itemOtherDescription = 'Other';
        $customizedBillOfMaterialsItem3->quantity = 2.3;
        $customizedBillOfMaterialsItem3->signalCodeDescription = 'LU';
        $customizedBillOfMaterialsItem3->itemSignalCode = 'LU';
        $customizedBillOfMaterialsItem3->engineeringSignalCode = 'LU';
        $customizedBillOfMaterialsItem3->engineeringRevision = 'B';
        $customizedBillOfMaterialsItem3->engineeringDescription = 'Eng desc';

        $billOfMaterialItem = new BillOfMaterialItem();
        $billOfMaterialItem->product = 'A';
        $billOfMaterialItem->itemDescription = 'Eng desc';
        $billOfMaterialItem->itemOtherDescription = 'Other';
        $billOfMaterialItem->signalCodeDescription = 'LU';
        $billOfMaterialItem->itemSignalCode = 'LU';
        $billOfMaterialItem->engineeringSignalCode = 'LU';
        $billOfMaterialItem->engineeringRevision = 'A';
        $billOfMaterialItem->engineeringDescription = 'Eng desc';
        $billOfMaterialItem->addItem($customizedBillOfMaterialsItem2);
        $billOfMaterialItem->addItem($customizedBillOfMaterialsItem3);

        $manualDocumentRepositoryMock = $this->createMock(EntityRepository::class);
        $manualDocumentRepositoryMock->expects($this->once())->method('findOneBy')->willReturn(new ManualDocumentCategory());

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(ManualDocumentCategory::class)->shouldBeCalledOnce()->willReturn($manualDocumentRepositoryMock);

        $manualPartFactoryProphecy = $this->prophesize(ManualPartFactory::class);
        $manualPartFactoryProphecy->createCollectionFromBillOfMaterialsItem(Argument::cetera())->shouldBeCalledTimes(1);

        $vaultFileFactoryProphecy = $this->prophesize(VaultFileFactory::class);
        $vaultPartFileProviderProphecy = $this->prophesize(VaultPartFileProvider::class);

        $manualDocumentFactory = new ManualDocumentFactory($entityManagerProphecy->reveal(), $vaultFileFactoryProphecy->reveal(), $manualPartFactoryProphecy->reveal(), $vaultPartFileProviderProphecy->reveal());
        $manualDocument = $manualDocumentFactory->createFromBillOfMaterials($billOfMaterialItem);

        $this->assertSame('A', $manualDocument->factoryNumber);
        $this->assertSame('A', $manualDocument->revision);
        $this->assertInstanceOf(ManualDocumentCategory::class, $manualDocument->category);
        $this->assertSame('Eng desc', $manualDocument->description);
        $this->assertSame('Other', $manualDocument->otherDescription);
        $this->assertSame(0.0, $manualDocument->quantity);
        $this->assertSame('', $manualDocument->type);
    }

    public function testCreateAManualDocumentVaultFile()
    {
        $manual = new Manual();
        $manualPart = new ManualPart();
        $manualPartsList = new ArrayCollection();
        $manualPartsList->add($manualPart);

        $customizedBillOfMaterialsItem = new ManualPartLn();
        $customizedBillOfMaterialsItem->partNumber = 'A';
        $customizedBillOfMaterialsItem->itemDescription = 'Eng desc';
        $customizedBillOfMaterialsItem->itemOtherDescription = 'Other';
        $customizedBillOfMaterialsItem->quantity = 2.3;
        $customizedBillOfMaterialsItem->signalCodeDescription = 'LU';
        $customizedBillOfMaterialsItem->engineeringSignalCode = 'LU';
        $customizedBillOfMaterialsItem->engineeringRevision = 'A';

        $customizedBillOfMaterialsItem2 = new ManualPartLn();
        $customizedBillOfMaterialsItem2->partNumber = 'A';
        $customizedBillOfMaterialsItem2->signalCodeDescription = 'LU';

        $customizedBillOfMaterialsItem3 = new ManualPartLn();
        $customizedBillOfMaterialsItem3->partNumber = 'B';
        $customizedBillOfMaterialsItem3->itemDescription = 'Eng desc';
        $customizedBillOfMaterialsItem3->itemOtherDescription = 'Other';
        $customizedBillOfMaterialsItem3->quantity = 2.3;
        $customizedBillOfMaterialsItem3->signalCodeDescription = 'LU';
        $customizedBillOfMaterialsItem3->engineeringSignalCode = 'LU';
        $customizedBillOfMaterialsItem3->engineeringRevision = 'B';
        $customizedBillOfMaterials = new Manuals();
        $customizedBillOfMaterials->addItem($customizedBillOfMaterialsItem);
        $customizedBillOfMaterials->addItem($customizedBillOfMaterialsItem2);
        $customizedBillOfMaterials->addItem($customizedBillOfMaterialsItem3);
        $customizedBillOfMaterials->site = 500;

        $manualDocumentRepositoryMock = $this->createMock(EntityRepository::class);
        $manualDocumentRepositoryMock->expects($this->once())->method('findOneBy')->willReturn(new ManualDocumentCategory());

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(ManualDocumentCategory::class)->shouldBeCalledOnce()->willReturn($manualDocumentRepositoryMock);

        $manualPartFactoryProphecy = $this->prophesize(ManualPartFactory::class);
        $manualPartFactoryProphecy->createCollectionFromCustomizedBillOfMaterialsItem(Argument::cetera())->shouldBeCalledTimes(2);

        $fileProphecy = $this->prophesize(File::class)->willBeConstructedWith(['we will miss you ariane', false]);
        $fileProphecy->getPathname()->shouldBeCalledOnce()->willReturn('we will miss you ariane');

        $vaultFileFactoryProphecy = $this->prophesize(VaultFileFactory::class);
        $vaultPartFileProviderProphecy = $this->prophesize(VaultPartFileProvider::class);
        $vaultPartFileProviderProphecy->getFile(Argument::cetera())->shouldBeCalledTimes(2)->willReturn($fileProphecy->reveal(), null);

        $manualDocumentFactory = new ManualDocumentFactory($entityManagerProphecy->reveal(), $vaultFileFactoryProphecy->reveal(), $manualPartFactoryProphecy->reveal(), $vaultPartFileProviderProphecy->reveal());
        $manualDocumentFactory->createCollectionFromCustomizedBillOfMaterials($customizedBillOfMaterials, $manual, 'chapters', true, true);

        $this->assertCount(2, $manual->getDocuments());
        $this->assertInstanceOf(ManualDocument::class, $manual->getDocuments()->first());
        $this->assertSame('A', $manual->getDocuments()->first()->factoryNumber);
        $this->assertSame('A', $manual->getDocuments()->first()->revision);
        $this->assertInstanceOf(ManualDocumentCategory::class, $manual->getDocuments()->first()->category);
        $this->assertSame('Eng desc', $manual->getDocuments()->first()->description);
        $this->assertSame('Other', $manual->getDocuments()->first()->otherDescription);
        $this->assertSame(2.3, $manual->getDocuments()->first()->quantity);
        $this->assertSame('MANUAL:SECTION', $manual->getDocuments()->first()->type);
        $this->assertCount(1, $manual->getDocuments()->first()->getFiles());
    }
}
