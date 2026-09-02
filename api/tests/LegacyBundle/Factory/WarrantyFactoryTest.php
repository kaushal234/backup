<?php

declare(strict_types=1);

namespace App\Tests\LegacyBundle\Factory;

use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Support\UnitOperationalStatus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use LegacyBundle\Entity\EquipmentRecord;
use LegacyBundle\Entity\SalesOrderLine;
use LegacyBundle\Entity\SalesOrderUnit;
use LegacyBundle\Entity\WarrantyClaim;
use LegacyBundle\Factory\WarrantyFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;

final class WarrantyFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateFromTechnicianOnCallSetsFilteringFlagToBeFiltered(): void
    {
        $salesOrderLine = new SalesOrderLine();
        $salesOrderLine->warrantyAccepted = 'Y';

        $salesOrderUnit = new SalesOrderUnit();
        $salesOrderUnit->salesOrderLine = $salesOrderLine;

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->salesOrderUnit = $salesOrderUnit;
        $equipmentRecord->customerName = 'customer_for_fur';
        $equipmentRecord->deliveryLocation = 'ONT';
        $equipmentRecord->type = 'Loaders';
        $equipmentRecord->model = '929';
        $equipmentRecord->factory = 'TLD SHE';
        $equipmentRecord->salesOrganisation = 'TLD SHE';
        $equipmentRecord->serialNumber = 'T1000';
        $equipmentRecord->hours = 724;
        $equipmentRecord->shippedDate = new \DateTime('2024-01-01');
        $equipmentRecord->warrantyLength = 12;
        $equipmentRecord->dateWarrantyEnd = new \DateTime('2025-01-01');
        $equipmentRecord->warrantyConditions = 'Standard';

        $repositoryProphecy = $this->prophesize(EntityRepository::class);
        $repositoryProphecy->find(42)->willReturn($equipmentRecord);

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(EquipmentRecord::class)->willReturn($repositoryProphecy->reveal());

        $user = new People();
        $user->setEmail('technician@tld.fr');

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->willReturn($user);

        $validatorProphecy = $this->prophesize(ValidatorInterface::class);

        $factory = new WarrantyFactory(
            $validatorProphecy->reveal(),
            $securityProphecy->reveal(),
            $entityManagerProphecy->reveal(),
        );

        $unitOperationalStatus = new UnitOperationalStatus();
        $unitOperationalStatus->setName('MCF');

        $appEquipmentRecord = $this->prophesize(\App\Entity\EquipmentRecord::class);
        $appEquipmentRecord->getLegacyId()->willReturn(42);

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->description = 'The customer reports that it does not work.';
        $technicianOnCall->unitOperationalStatus = $unitOperationalStatus;
        $technicianOnCall->equipmentRecord = $appEquipmentRecord->reveal();

        $warranty = $factory->createFromTechnicianOnCall($technicianOnCall);

        self::assertSame(WarrantyClaim::TO_BE_FILTERED, $warranty->filteringFlag);
        self::assertSame(WarrantyClaim::PENDING, $warranty->status);
    }
}
