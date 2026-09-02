<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler\Support;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\OrderToFactory;
use App\Message\Support\EquipmentRecordGreenTagUpdate;
use App\MessageHandler\Support\EquipmentRecordGreenTagUpdateHandler;
use App\Notifier\Support\EquipmentRecord\EquipmentRecordNotifier;
use App\Notifier\Tasks\SequenceNotifier;
use App\Repository\Directory\LocationRepository;
use App\Repository\Directory\PeopleRepository;
use LegacyBundle\Manager\SequenceManager;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EquipmentRecordGreenTagUpdateHandlerTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider dataProvider
     */
    public function testHandlerWhenEquipmentRecordGreenTagDateIsUpdated(\DateTime $greenTagDate, bool $isSequenceCreated, ?string $notifiedGroup = null): void
    {
        $sequenceManagerProphecy = $this->prophesize(SequenceManager::class);
        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findGroupsMembers', 'findGroupMembers'])->getMock();
        $locationRepositoryMock = $this->getMockBuilder(LocationRepository::class)->disableOriginalConstructor()->onlyMethods(['findOneBy'])->getMock();
        $sequenceNotifierProphecy = $this->prophesize(SequenceNotifier::class);
        $equipmentRecordNotifierProphecy = $this->prophesize(EquipmentRecordNotifier::class);
        $messageProphecy = $this->prophesize(EquipmentRecordGreenTagUpdate::class);
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);

        $factoryOrder = new OrderToFactory();
        $factoryOrder->factoryPromisedDeliveryDate = new \DateTime();

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->orderFactory = $factoryOrder;
        $equipmentRecord
            ->setLegacyId(1)
            ->setManufacturerLocation($locationManufacturer = (new Location())->setName('Montégu'))
            ->setSerialNumber('SN123456')
            ->setSalesOrganisation($locationSso = (new Location())->setName('Nantes'))
            ->setModel('BroumBroum')
            ->setBuyer((new Customer())->setName('Jean Ladigue'))
            ->setEstimatedGreenTagDate(new \DateTime())
            ->setGreenTagDate($greenTagDate)
            ->setFirstGreenTagDate($greenTagDate)
        ;

        $messageProphecy->getEquipmentRecordIri()->shouldBeCalledTimes(1)->willReturn('/equipmentRecord/1');
        $messageProphecy->getUserIri()->shouldBeCalledTimes(1)->willReturn('/user/1');
        $messageProphecy->getPreviousGreenTagDate()->shouldBeCalledTimes(1)->willReturn($previousGreenTagDate = '0000-00-00');

        $iriConverterProphecy->getResourceFromIri('/equipmentRecord/1')->shouldBeCalledTimes(1)->willReturn($equipmentRecord);
        $iriConverterProphecy->getResourceFromIri('/user/1')->shouldBeCalledTimes(1)->willReturn(new People());

        if ($isSequenceCreated) {
            $peopleRepositoryMock->expects($this->exactly(2))->method('findGroupsMembers')->withConsecutive([['role_SAM', 'role_CEO'], $locationSso], [['role_COO', 'role_QAM', 'role_PSM', 'role_PSE', 'role_PM', 'role_PS'], $locationManufacturer]);
            if (null !== $notifiedGroup) {
                if ('role_GCOO' === $notifiedGroup) {
                    $locationRepositoryMock->expects($this->once())->method('findOneBy')->with(['erp' => 900])->willReturn(new Location());
                }
                $peopleRepositoryMock->expects($this->once())->method('findGroupMembers')->with($notifiedGroup, $this->callback(static fn ($location) => $location instanceof Location));
            }
            $sequenceManagerProphecy->insert(Argument::any())->shouldBeCalledOnce();
            $sequenceNotifierProphecy->sendEmail(Argument::any(), Argument::any(), Argument::any(), Argument::any())->shouldBeCalledOnce();
        } else {
            $sequenceManagerProphecy->insert(Argument::any())->shouldNotHaveBeenCalled();
            $sequenceNotifierProphecy->sendEmail(Argument::any())->shouldNotHaveBeenCalled();
        }
        $equipmentRecordNotifierProphecy->sendGreenTagDateUpdateEmail($equipmentRecord, $previousGreenTagDate)->shouldBeCalledOnce();

        $handler = new EquipmentRecordGreenTagUpdateHandler($sequenceManagerProphecy->reveal(), $peopleRepositoryMock, $locationRepositoryMock, $sequenceNotifierProphecy->reveal(), $equipmentRecordNotifierProphecy->reveal(), $iriConverterProphecy->reveal());
        $handler($messageProphecy->reveal());
    }

    public function dataProvider(): \Generator
    {
        yield 'today date' => [new \DateTime('+ 2 month'), false];
        yield 'last day of the month' => [new \DateTime('last day of this month'), true, 'role_GCOO'];
        yield 'last day of the month - 1 day' => [(new \DateTime('last day of this month'))->modify('- 1 days'), true, 'role_RCEO'];
        yield 'last day of the month - 2 days' => [(new \DateTime('last day of this month'))->modify('- 2 days'), true];
    }
}
