<?php

declare(strict_types=1);

namespace App\Tests\DataProcessor\Support;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use ApiPlatform\Validator\ValidatorInterface;
use App\DataProcessor\Support\ManualDataProcessor;
use App\Dto\Support\ManualInput;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Support\Component;
use App\Entity\Support\EquipmentSerial;
use App\Entity\Support\Manual;
use App\Entity\Support\ManualDocument;
use App\Factory\Support\ManualFactory;
use App\Factory\VaultFileFactory;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\Manuals;
use App\Repository\Support\EquipmentSerialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;

class ManualDataProcessorTest extends TestCase
{
    use ProphecyTrait;

    public function testDryRun()
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $dataProviderProphecy = $this->prophesize(CachedIONItemDataProvider::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $manualFactoryProphecy = $this->prophesize(ManualFactory::class);
        $vaultFileFactoryProphecy = $this->prophesize(VaultFileFactory::class);
        $messageBusProphecy = $this->prophesize(MessageBusInterface::class);
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $resourceMetadataFactory = $this->prophesize(ResourceMetadataCollectionFactoryInterface::class);

        $manualInput = (new ManualInput());
        $manualInput->mainEquipmentRecord = $equipmentRecord = new EquipmentRecord();
        $manualInput->force = false;
        $validatorProphecy->validate($manualInput)->shouldBeCalledOnce();

        $manualFactoryProphecy->create($equipmentRecord, 'en', false, 'RELEASED')->shouldBeCalledOnce()->willReturn($manual = new Manual());
        $validatorProphecy->validate($manual, ['groups' => [Manual::CRITICAL_VALIDATION_GROUP]])->shouldBeCalledOnce();

        $entityManagerProphecy->persist($equipmentRecord)->shouldBeCalledOnce();
        $entityManagerProphecy->flush()->shouldBeCalledOnce();

        $validatorProphecy->validate($manual, ['groups' => Manual::NONCRITICAL_VALIDATION_GROUP]);

        $manualDataPersister = new ManualDataProcessor(
            $validatorProphecy->reveal(),
            $manualFactoryProphecy->reveal(),
            $entityManagerProphecy->reveal(),
            $vaultFileFactoryProphecy->reveal(),
            $dataProviderProphecy->reveal(),
            $messageBusProphecy->reveal(),
            $iriConverterProphecy->reveal(),
            $securityProphecy->reveal(),
            $resourceMetadataFactory->reveal(),
        );

        $manualDataPersister->process($manualInput, new Post());
    }

    public function testManualSerialIsCreated()
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $dataProviderProphecy = $this->prophesize(CachedIONItemDataProvider::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $manualFactoryProphecy = $this->prophesize(ManualFactory::class);
        $vaultFileFactoryProphecy = $this->prophesize(VaultFileFactory::class);
        $messageBusProphecy = $this->prophesize(MessageBusInterface::class);
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $resourceMetadataFactoryProphecy = $this->prophesize(ResourceMetadataCollectionFactoryInterface::class);

        $date = new \DateTime();

        $location = new Location();
        $location->setErp(7);

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord
            ->setManufacturerLocation($location)
            ->setProjectNumber('7')
            ->setGreenTagDate($date)
        ;

        $manual = new Manual();
        $manual->equipmentRecord = $equipmentRecord;
        $manual->language = 'en';
        $manual->setLegacyId(3615);

        $manualInput = new ManualInput();
        $manualInput->force = true;
        $manualInput->mainEquipmentRecord = $equipmentRecord;

        $validatorProphecy->validate($manualInput)->shouldBeCalledOnce();

        $manualFactoryProphecy->create($equipmentRecord, 'en', true, 'RELEASED')->shouldBeCalledOnce()->willReturn($manual);
        $validatorProphecy->validate($manual, ['groups' => [Manual::PUBLISHABLE_VALIDATION_GROUP]])->shouldBeCalledOnce();

        $entityManagerProphecy->persist($equipmentRecord)->shouldNotBeCalled();

        $operation = new Get(class: Manuals::class);
        $resourceMetadataFactoryProphecy->create(Manuals::class)->shouldBeCalledOnce()->willReturn(
            new ResourceMetadataCollection(Manuals::class, [new ApiResource(operations: [$operation])])
        );

        $dataProviderProphecy->provide(
            $operation,
            ['site' => 7, 'project' => '7'],
            [
                'operation_type' => Get::class,
                '_ion_data_area' => [
                    'date' => $date->format(\DateTimeInterface::ATOM),
                    'signalCodeFilter' => ManualFactory::getFormattedGroupSignaCode('schematics'),
                    'signalCodeFilterMethod' => 'Equals',
                    'signalCodeAttribute' => 'engineeringSignalCode',
                    'otherLanguage' => 'en',
                ],
            ]
        )->shouldBeCalledOnce()->willReturn(new CustomizedBillOfMaterials());

        $componentRepositoryMock = $this->createMock(EntityRepository::class);
        $componentRepositoryMock->expects($this->once())->method('findOneBy')->willReturn(new Component());

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(Argument::cetera())->shouldBeCalledTimes(1)->willReturn($componentRepositoryMock);
        $entityManagerProphecy->persist(Argument::type(Manual::class))->shouldBeCalledOnce();
        $entityManagerProphecy->flush()->shouldBeCalledTimes(2);
        $entityManagerProphecy->persist(Argument::type(EquipmentSerial::class))->shouldBeCalledOnce();

        $manualDataProcessor = new ManualDataProcessor(
            $validatorProphecy->reveal(),
            $manualFactoryProphecy->reveal(),
            $entityManagerProphecy->reveal(),
            $vaultFileFactoryProphecy->reveal(),
            $dataProviderProphecy->reveal(),
            $messageBusProphecy->reveal(),
            $iriConverterProphecy->reveal(),
            $securityProphecy->reveal(),
            $resourceMetadataFactoryProphecy->reveal(),
        );

        $manualDataProcessor->process($manualInput, new Post());

        $this->assertInstanceOf(EquipmentSerial::class, $manual->equipmentSerial);
        $this->assertSame('3615', $manual->equipmentSerial->serial);
        $this->assertInstanceOf(Component::class, $manual->equipmentSerial->component);
    }

    public function testSchematicsSerialsAreCreated()
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $dataProviderProphecy = $this->prophesize(CachedIONItemDataProvider::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $manualFactoryProphecy = $this->prophesize(ManualFactory::class);
        $vaultFileFactoryProphecy = $this->prophesize(VaultFileFactory::class);
        $messageBusProphecy = $this->prophesize(MessageBusInterface::class);
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $resourceMetadataFactoryProphecy = $this->prophesize(ResourceMetadataCollectionFactoryInterface::class);

        $date = new \DateTime();
        $location = new Location();
        $location->setErp(7);

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord
            ->setManufacturerLocation($location)
            ->setProjectNumber('7')
            ->setGreenTagDate($date)
        ;

        $manual = new Manual();
        $manual->equipmentRecord = $equipmentRecord;
        $manual->language = 'en';
        $manual->setLegacyId(42);

        $manualInput = new ManualInput();
        $manualInput->force = true;
        $manualInput->mainEquipmentRecord = $equipmentRecord;

        $customizedBillOfMaterialsItem = new CustomizedBillOfMaterials\ManualPart();
        $customizedBillOfMaterialsItem->partNumber = '5';
        $customizedBillOfMaterialsItem->itemDescription = 'Item desc';
        $customizedBillOfMaterialsItem->engineeringSignalCode = 'CO';

        $customizedBillOfMaterials = new Manuals();
        $customizedBillOfMaterials->addItem($customizedBillOfMaterialsItem);
        $customizedBillOfMaterials->addItem(clone $customizedBillOfMaterialsItem);

        $validatorProphecy->validate($manualInput)->shouldBeCalledOnce();

        $manualFactoryProphecy->create($equipmentRecord, 'en', true, 'RELEASED')->shouldBeCalledOnce()->willReturn($manual);
        $validatorProphecy->validate($manual, ['groups' => [Manual::PUBLISHABLE_VALIDATION_GROUP]])->shouldBeCalledOnce();

        $entityManagerProphecy->persist($equipmentRecord)->shouldNotBeCalled();

        $operation = new Get(class: Manuals::class);
        $resourceMetadataFactoryProphecy->create(Manuals::class)->shouldBeCalledOnce()->willReturn(
            new ResourceMetadataCollection(Manuals::class, [new ApiResource(operations: [$operation])])
        );

        $dataProviderProphecy->provide(
            $operation,
            ['site' => 7, 'project' => '7'],
            [
                'operation_type' => Get::class,
                '_ion_data_area' => [
                    'date' => $date->format(\DateTimeInterface::ATOM),
                    'signalCodeFilter' => ManualFactory::getFormattedGroupSignaCode('schematics'),
                    'signalCodeFilterMethod' => 'Equals',
                    'signalCodeAttribute' => 'engineeringSignalCode',
                    'otherLanguage' => 'en',
                ],
            ]
        )->shouldBeCalledOnce()->willReturn($customizedBillOfMaterials);

        $componentRepositoryMock = $this->createMock(EntityRepository::class);
        $componentRepositoryMock->expects($this->exactly(2))->method('findOneBy')->willReturn(new Component());

        $equipmentSerialRepositoryMock = $this->getMockBuilder(EquipmentSerialRepository::class)->disableOriginalConstructor()->onlyMethods(['createOrFindExistingSerial'])->getMock();
        $equipmentSerialRepositoryMock->expects($this->once())->method('createOrFindExistingSerial')->willReturn(new EquipmentSerial());

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(EquipmentSerial::class)->shouldBeCalledTimes(1)->willReturn($equipmentSerialRepositoryMock);
        $entityManagerProphecy->getRepository(Component::class)->shouldBeCalledTimes(2)->willReturn($componentRepositoryMock);

        $entityManagerProphecy->persist(Argument::that(static function (object $equipmentSerial) {
            return $equipmentSerial instanceof EquipmentSerial && '42' === $equipmentSerial->serial;
        }))->shouldBeCalledTimes(1);

        $entityManagerProphecy->persist($manual)->shouldBeCalledTimes(1);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(2);

        $manualDataProcessor = new ManualDataProcessor(
            $validatorProphecy->reveal(),
            $manualFactoryProphecy->reveal(),
            $entityManagerProphecy->reveal(),
            $vaultFileFactoryProphecy->reveal(),
            $dataProviderProphecy->reveal(),
            $messageBusProphecy->reveal(),
            $iriConverterProphecy->reveal(),
            $securityProphecy->reveal(),
            $resourceMetadataFactoryProphecy->reveal(),
        );
        $manualDataProcessor->process($manualInput, new Post());
    }

    public function testSchematicsAreEmpty()
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $dataProviderProphecy = $this->prophesize(CachedIONItemDataProvider::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $manualFactoryProphecy = $this->prophesize(ManualFactory::class);
        $vaultFileFactoryProphecy = $this->prophesize(VaultFileFactory::class);
        $messageBusProphecy = $this->prophesize(MessageBusInterface::class);
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $resourceMetadataFactoryProphecy = $this->prophesize(ResourceMetadataCollectionFactoryInterface::class);

        $location = new Location();
        $location->setErp(7);

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord
            ->setManufacturerLocation($location)
            ->setProjectNumber('7')
            ->setGreenTagDate($date = new \DateTime())
        ;

        $manual = new Manual();
        $manual->equipmentRecord = $equipmentRecord;
        $manual->language = 'en';
        $manual->setLegacyId(42);

        $manualInput = new ManualInput();
        $manualInput->force = true;
        $manualInput->mainEquipmentRecord = $equipmentRecord;

        $validatorProphecy->validate($manualInput)->shouldBeCalledOnce();

        $manualFactoryProphecy->create($equipmentRecord, 'en', true, 'RELEASED')->shouldBeCalledOnce()->willReturn($manual);
        $validatorProphecy->validate($manual, ['groups' => [Manual::PUBLISHABLE_VALIDATION_GROUP]])->shouldBeCalledOnce();

        $entityManagerProphecy->persist($equipmentRecord)->shouldNotBeCalled();

        $operation = new Get(class: Manuals::class);
        $resourceMetadataFactoryProphecy->create(Manuals::class)->shouldBeCalledOnce()->willReturn(
            new ResourceMetadataCollection(Manuals::class, [new ApiResource(operations: [$operation])])
        );

        $dataProviderProphecy->provide(
            $operation,
            ['site' => 7, 'project' => '7'],
            [
                'operation_type' => Get::class,
                '_ion_data_area' => [
                    'date' => $date->format(\DateTimeInterface::ATOM),
                    'signalCodeFilter' => ManualFactory::getFormattedGroupSignaCode('schematics'),
                    'signalCodeFilterMethod' => 'Equals',
                    'signalCodeAttribute' => 'engineeringSignalCode',
                    'otherLanguage' => 'en',
                ],
            ]
        )->shouldBeCalledOnce()->willReturn(null);

        $componentRepositoryMock = $this->createMock(EntityRepository::class);
        $componentRepositoryMock->expects($this->once())->method('findOneBy')->willReturn(new Component());

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(Argument::cetera())->shouldBeCalledOnce()->willReturn($componentRepositoryMock);

        $entityManagerProphecy->persist(Argument::type(EquipmentSerial::class))->shouldBeCalledOnce();
        $entityManagerProphecy->persist($manual)->shouldBeCalledOnce();
        $entityManagerProphecy->flush()->shouldBeCalledTimes(2);

        $manualDataProcessor = new ManualDataProcessor(
            $validatorProphecy->reveal(),
            $manualFactoryProphecy->reveal(),
            $entityManagerProphecy->reveal(),
            $vaultFileFactoryProphecy->reveal(),
            $dataProviderProphecy->reveal(),
            $messageBusProphecy->reveal(),
            $iriConverterProphecy->reveal(),
            $securityProphecy->reveal(),
            $resourceMetadataFactoryProphecy->reveal(),
        );
        $manualDataProcessor->process($manualInput, new Post());
    }

    public function testDocumentAreStored()
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $dataProviderProphecy = $this->prophesize(CachedIONItemDataProvider::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $manualFactoryProphecy = $this->prophesize(ManualFactory::class);
        $vaultFileFactoryProphecy = $this->prophesize(VaultFileFactory::class);
        $messageBusProphecy = $this->prophesize(MessageBusInterface::class);
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $resourceMetadataFactoryProphecy = $this->prophesize(ResourceMetadataCollectionFactoryInterface::class);

        $location = new Location();
        $location->setErp(7);

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord
            ->setManufacturerLocation($location)
            ->setProjectNumber('7')
            ->setGreenTagDate($date = new \DateTime())
        ;

        $manualDocument1 = new ManualDocument();
        $manualDocument1->type = '3615 Ulla';

        $manualDocument2 = new ManualDocument();
        $manualDocument2->type = '3615 Ulla';

        $manual = new Manual();
        $manual->equipmentRecord = $equipmentRecord;
        $manual->language = 'en';
        $manual->setLegacyId(3615);
        $manual->addDocument($manualDocument1);
        $manual->addDocument($manualDocument2);

        $manualInput = new ManualInput();
        $manualInput->force = true;
        $manualInput->mainEquipmentRecord = $equipmentRecord;

        $validatorProphecy->validate($manualInput)->shouldBeCalledOnce();

        $manualFactoryProphecy->create($equipmentRecord, 'en', true, 'RELEASED')->shouldBeCalledOnce()->willReturn($manual);
        $validatorProphecy->validate($manual, ['groups' => [Manual::PUBLISHABLE_VALIDATION_GROUP]])->shouldBeCalledOnce();

        $entityManagerProphecy->persist($equipmentRecord)->shouldNotBeCalled();

        $operation = new Get(class: Manuals::class);
        $resourceMetadataFactoryProphecy->create(Manuals::class)->shouldBeCalledOnce()->willReturn(
            new ResourceMetadataCollection(Manuals::class, [new ApiResource(operations: [$operation])])
        );

        $dataProviderProphecy->provide(
            $operation,
            ['site' => 7, 'project' => '7'],
            [
                'operation_type' => Get::class,
                '_ion_data_area' => [
                    'date' => $date->format(\DateTimeInterface::ATOM),
                    'signalCodeFilter' => ManualFactory::getFormattedGroupSignaCode('schematics'),
                    'signalCodeFilterMethod' => 'Equals',
                    'signalCodeAttribute' => 'engineeringSignalCode',
                    'otherLanguage' => 'en',
                ],
            ]
        )->shouldBeCalledOnce()->willReturn(new Manuals());

        $componentRepositoryMock = $this->createMock(EntityRepository::class);
        $componentRepositoryMock->expects($this->once())->method('findOneBy')->willReturn(new Component());

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(Argument::cetera())->shouldBeCalledTimes(1)->willReturn($componentRepositoryMock);
        $entityManagerProphecy->persist(Argument::any())->shouldBeCalled();
        $entityManagerProphecy->flush()->shouldBeCalled();

        $vaultFileFactoryProphecy->store($manualDocument1, 7, 'jpg', false)->shouldBeCalledTimes(2);

        $manualDataProcessor = new ManualDataProcessor(
            $validatorProphecy->reveal(),
            $manualFactoryProphecy->reveal(),
            $entityManagerProphecy->reveal(),
            $vaultFileFactoryProphecy->reveal(),
            $dataProviderProphecy->reveal(),
            $messageBusProphecy->reveal(),
            $iriConverterProphecy->reveal(),
            $securityProphecy->reveal(),
            $resourceMetadataFactoryProphecy->reveal(),
        );
        $manualDataProcessor->process($manualInput, new Post());

        $this->assertInstanceOf(EquipmentSerial::class, $manual->equipmentSerial);
        $this->assertSame('3615', $manual->equipmentSerial->serial);
        $this->assertInstanceOf(Component::class, $manual->equipmentSerial->component);
    }
}
