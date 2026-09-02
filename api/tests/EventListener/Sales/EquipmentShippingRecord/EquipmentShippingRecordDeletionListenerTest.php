<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Sales\EquipmentShippingRecord;

use App\Entity\EquipmentRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\EventListener\Sales\EquipmentShippingRecord\EquipmentShippingRecordDeletionListener;
use LegacyBundle\Manager\CustomerServiceRecordManager;
use LegacyBundle\Manager\EquipmentRecordManager;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EquipmentShippingRecordDeletionListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @dataProvider deleteEventProvider
     */
    public function testEquipmentShippingRecordDeletionPreValidate(object $controllerResult, bool $isPostMethod, int $expectedCall)
    {
        $customerServiceRecordManager = $this->prophesize(CustomerServiceRecordManager::class);
        $customerServiceRecordManager->getCustomerServiceRecordByEquipmentRecord(Argument::type('integer'))->shouldBeCalledTimes($expectedCall);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(CustomerServiceRecordManager::class)->shouldBeCalledTimes($expectedCall)->willReturn($customerServiceRecordManager->reveal());

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->isMethod(Request::METHOD_DELETE)->willReturn($isPostMethod);

        $listener = new EquipmentShippingRecordDeletionListener($serviceLocatorProphecy->reveal());
        $listener->validateNoCustomerServiceRecord(new ViewEvent(static::$kernel, $requestProphecy->reveal(), HttpKernelInterface::MAIN_REQUEST, $controllerResult));
    }

    public function testEquipmentShippingRecordDeletionOnClosedESR()
    {
        $this->expectException(BadRequestException::class);

        $equipmentShippingRecordClosed = new EquipmentShippingRecord();
        $equipmentShippingRecordClosed->setStatus(EquipmentShippingRecord::CLOSED);

        $customerServiceRecordManager = $this->prophesize(CustomerServiceRecordManager::class);
        $customerServiceRecordManager->getCustomerServiceRecordByEquipmentRecord(Argument::type('integer'))->shouldBeCalledTimes(0);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(CustomerServiceRecordManager::class)->shouldBeCalledTimes(0);

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->isMethod(Request::METHOD_DELETE)->willReturn(true);

        $listener = new EquipmentShippingRecordDeletionListener($serviceLocatorProphecy->reveal());
        $listener->validateNotClosed(new ViewEvent(static::$kernel, $requestProphecy->reveal(), HttpKernelInterface::MAIN_REQUEST, $equipmentShippingRecordClosed));
    }

    public function testEquipmentShippingRecordDeletionWithACommissionningCsr()
    {
        $this->expectException(BadRequestException::class);

        $equipmentShippingRecordWithACommissioningCsr = new EquipmentShippingRecord();
        $equipmentShippingRecordLine = new EquipmentShippingRecordLine();
        $equipmentShippingRecordLine->equipmentRecord = (new EquipmentRecord())->setLegacyId(25);
        $equipmentShippingRecordWithACommissioningCsr->addEquipmentShippingRecordLine($equipmentShippingRecordLine);

        $customerServiceRecordManager = $this->prophesize(CustomerServiceRecordManager::class);
        $customerServiceRecordManager->getCustomerServiceRecordByEquipmentRecord(Argument::type('integer'))->shouldBeCalledTimes(1)->willReturn(['je suis un csr']);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(CustomerServiceRecordManager::class)->shouldBeCalledTimes(1)->willReturn($customerServiceRecordManager->reveal());

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->isMethod(Request::METHOD_DELETE)->willReturn(true);

        $routerInterface = $this->prophesize(RouterInterface::class);
        $routerInterface->generate('legacy_product_support', ['m' => ['equipment', 'view', 'csr'], 'id' => $equipmentShippingRecordLine->equipmentRecord->getLegacyId()])->shouldBeCalledTimes(1)->willReturn('une route');
        $serviceLocatorProphecy->get(RouterInterface::class)->shouldBeCalledTimes(1)->willReturn($routerInterface->reveal());

        $translatorInterface = $this->prophesize(TranslatorInterface::class);
        $translatorInterface->trans('csr.csr', ['%id%' => $equipmentShippingRecordLine->equipmentRecord->getLegacyId()], 'emails')->shouldBeCalledTimes(1)->willReturn('une traduction');
        $serviceLocatorProphecy->get(TranslatorInterface::class)->shouldBeCalledTimes(1)->willReturn($translatorInterface->reveal());

        $listener = new EquipmentShippingRecordDeletionListener($serviceLocatorProphecy->reveal());
        $listener->validateNoCustomerServiceRecord(new ViewEvent(static::$kernel, $requestProphecy->reveal(), HttpKernelInterface::MAIN_REQUEST, $equipmentShippingRecordWithACommissioningCsr));
    }

    /**
     * @dataProvider deleteEventProvider
     */
    public function testEquipmentShippingRecordDeletionPostWrite(object $controllerResult, bool $isPostMethod, int $expectedCall)
    {
        $equipmentRecordManagerProphecy = $this->prophesize(EquipmentRecordManager::class);
        $equipmentRecordManagerProphecy->unsetEsrIdProperty(Argument::type(EquipmentShippingRecord::class), Argument::type(EquipmentRecord::class))->shouldBeCalledTimes($expectedCall);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(EquipmentRecordManager::class)->shouldBeCalledTimes($expectedCall)->willReturn($equipmentRecordManagerProphecy->reveal());

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->isMethod(Request::METHOD_DELETE)->willReturn($isPostMethod);

        $listener = new EquipmentShippingRecordDeletionListener($serviceLocatorProphecy->reveal());
        $listener->updateLegacyEquipmentRecord(new ViewEvent(static::$kernel, $requestProphecy->reveal(), HttpKernelInterface::MAIN_REQUEST, $controllerResult));
    }

    public function deleteEventProvider()
    {
        $equipmentShippingRecordWithoutLines = new EquipmentShippingRecord();

        $equipmentShippingRecordWithOneLines = new EquipmentShippingRecord();
        // ESR with one line
        $equipmentShippingRecordLine = new EquipmentShippingRecordLine();
        $equipmentShippingRecordLine->equipmentRecord = (new EquipmentRecord())->setLegacyId(15);
        $equipmentShippingRecordWithOneLines->addEquipmentShippingRecordLine($equipmentShippingRecordLine);

        // ESR with 2 line
        $equipmentShippingRecordWithTwoLines = new EquipmentShippingRecord();
        $equipmentShippingRecordLine1 = new EquipmentShippingRecordLine();
        $equipmentShippingRecordLine1->equipmentRecord = (new EquipmentRecord())->setLegacyId(16);
        $equipmentShippingRecordWithTwoLines->addEquipmentShippingRecordLine($equipmentShippingRecordLine1);

        $equipmentShippingRecordLine2 = new EquipmentShippingRecordLine();
        $equipmentShippingRecordLine2->equipmentRecord = (new EquipmentRecord())->setLegacyId(15);
        $equipmentShippingRecordWithTwoLines->addEquipmentShippingRecordLine($equipmentShippingRecordLine2);

        yield 'delete ESR without line' => [$equipmentShippingRecordWithoutLines, true, 0];
        yield 'delete ESR with one Line' => [$equipmentShippingRecordWithOneLines, true, 1];
        yield 'delete ESR with 2 Lines' => [$equipmentShippingRecordWithTwoLines, true, 2];
        yield 'Not a POST method' => [$equipmentShippingRecordWithoutLines, false, 0];
        yield 'Not a EquipmentShippingRecord' => [new \stdClass(), true, 0];
        yield 'Not a POST method and not a EquipmentShippingRecord' => [new \stdClass(), false, 0];
    }
}
