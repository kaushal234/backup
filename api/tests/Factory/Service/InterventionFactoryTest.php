<?php

declare(strict_types=1);

namespace App\Tests\Factory\Service;

use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\Factory\Service\InterventionFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;

class InterventionFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateFromEmptyCustomerServiceRecord()
    {
        $this->expectException(\TypeError::class);
        $customerServiceRecord = new CustomerServiceRecord();

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->willReturn(new People());
        $interventionFactory = new InterventionFactory($securityProphecy->reveal());

        $interventionFactory->createFromCustomerServiceRecord($customerServiceRecord);
    }

    public function testCreateFromCustomerServiceRecord()
    {
        $people = new People();

        $customerServiceRecord = new CustomerServiceRecord();
        $customerServiceRecord->leader = $people;
        $customerServiceRecord->plannedAt = new \DateTime();

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->willReturn(new People());
        $interventionFactory = new InterventionFactory($securityProphecy->reveal());

        $intervention = $interventionFactory->createFromCustomerServiceRecord($customerServiceRecord);

        self::assertSame($people, $intervention->leader);
        $intervention->plannedAt = $customerServiceRecord->plannedAt;
    }

    public function testCreateFromCustomerServiceRecordWithPlannedDate()
    {
        $people = new People();
        $plannedAt = new \DateTime();

        $customerServiceRecord = new CustomerServiceRecord();
        $customerServiceRecord->leader = $people;
        $customerServiceRecord->plannedAt = $plannedAt;

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->willReturn(new People());
        $interventionFactory = new InterventionFactory($securityProphecy->reveal());

        $intervention = $interventionFactory->createFromCustomerServiceRecord($customerServiceRecord);

        self::assertSame($people, $intervention->leader);
        self::assertSame($plannedAt, $intervention->plannedAt);
    }
}
