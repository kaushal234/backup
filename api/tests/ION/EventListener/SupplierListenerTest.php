<?php

declare(strict_types=1);

namespace App\Tests\ION\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\Location;
use App\Entity\SupplierEntityInterface;
use App\ION\EventListener\MasterData\BusinessPartners\SupplierListener;
use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerManager;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SupplierListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testObjectsImplementingTheInterfaceAreHandledForPostRequests()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $managerRepository = $this->prophesize(BusinessPartnerManager::class);

        $supplier = new BusinessPartner();
        $supplier->code = 'SUNONONONONO';
        $supplier->name = 'OH NO HAVE MERCY, I SUPPLIER YOU';
        $managerRepository->findSupplier('SUNONONONONO')->shouldBeCalledTimes(1)->willReturn($supplier);

        $serviceLocatorProphecy->get(BusinessPartnerManager::class)->shouldBeCalledTimes(1)->willReturn($managerRepository->reveal());

        $listener = new SupplierListener($serviceLocatorProphecy->reveal());

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $supplierEntity = new class implements SupplierEntityInterface {
            private string $supplierName;

            public function getSupplierNumber(): string
            {
                return 'SUNONONONONO';
            }

            public function setSupplierName(string $name): SupplierEntityInterface
            {
                $this->supplierName = $name;

                return $this;
            }

            public function getLocation(): Location
            {
                return (new Location())->setErpSoftware(Location::TLD_ERP_SOFTWARE);
            }

            public function getSupplierName(): string
            {
                return $this->supplierName;
            }

            public function getBusinessPartnerCode(): string
            {
                return $this->getSupplierNumber();
            }
        };

        $listener->setSupplierName(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $supplierEntity));

        $this->assertSame($supplierEntity->getSupplierName(), 'OH NO HAVE MERCY, I SUPPLIER YOU');
    }

    public function testItRunsBeforeValidationSoDerivedSupplierNameCanSatisfyValidationConstraints()
    {
        $events = SupplierListener::getSubscribedEvents();
        $priority = $events[KernelEvents::VIEW][0][1];

        $this->assertGreaterThanOrEqual(
            EventPriorities::PRE_VALIDATE,
            $priority,
            'supplierName must be derived from supplierNumber before validation runs, otherwise validation groups requiring supplierName (e.g. FirstArticleQualification\'s "buyer" group) always fail.'
        );
    }
}
