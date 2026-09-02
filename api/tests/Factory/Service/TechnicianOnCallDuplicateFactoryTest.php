<?php

declare(strict_types=1);

namespace App\Tests\Factory\Service;

use App\Dto\Service\TechnicianOnCallDuplicateLineInput;
use App\Entity\Common\Airport;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\User;
use App\Factory\Service\TechnicianOnCallDuplicateFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class TechnicianOnCallDuplicateFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testCloneFromOriginalUsesClone(): void
    {
        $factory = new TechnicianOnCallDuplicateFactory();

        $original = new TechnicianOnCall();
        $original->confidential = true;
        $original->serialNumber = 'SN-KEEP?';
        $original->status = TechnicianOnCall::IN_PROGRESS;

        $clone = $factory->cloneFromOriginal($original);

        self::assertNotSame($original, $clone);

        self::assertFalse($clone->confidential);
        self::assertNull($clone->serialNumber);
    }

    public function testApplyInputLineOverridesFieldsAndResetsSomeFlags(): void
    {
        $factory = new TechnicianOnCallDuplicateFactory();

        $toc = new TechnicianOnCall();
        $toc->createdBy = new User();

        $input = new TechnicianOnCallDuplicateLineInput();
        $input->hourMeter = 1234;

        $equipmentRecordProphecy = $this->prophesize(EquipmentRecord::class);
        $endUser = new Customer();
        $equipmentRecordProphecy->getEndUser()->willReturn($endUser);

        $input->equipmentRecord = $equipmentRecordProphecy->reveal();
        $input->airport = $this->prophesize(Airport::class)->reveal();
        $input->salesOrganisationService = $this->prophesize(Location::class)->reveal();
        $input->nestedCustomerServiceRecord = null;

        $result = $factory->applyInputLine($toc, $input);

        self::assertSame($toc, $result);

        self::assertSame($input->equipmentRecord, $result->equipmentRecord);
        self::assertSame($input->airport, $result->airport);
        self::assertSame($input->salesOrganisationService, $result->salesOrganisationService);
        self::assertSame(TechnicianOnCall::IN_PROGRESS, $result->status);
        self::assertNull($result->createdBy);
        self::assertSame($input->nestedCustomerServiceRecord, $result->nestedCustomerServiceRecord);
        self::assertSame(1234, $result->hourMeter);
        self::assertFalse($result->factoryFlag);
        self::assertSame($endUser, $result->customer);
    }

    public function testDuplicateClonesAndAppliesInput(): void
    {
        $factory = new TechnicianOnCallDuplicateFactory();

        $original = new TechnicianOnCall();
        $original->confidential = true;
        $original->serialNumber = 'SN-ORIGINAL';
        $original->factoryFlag = true;

        $input = new TechnicianOnCallDuplicateLineInput();

        $equipmentRecordProphecy = $this->prophesize(EquipmentRecord::class);
        $endUser = new Customer();
        $equipmentRecordProphecy->getEndUser()->willReturn($endUser);

        $input->equipmentRecord = $equipmentRecordProphecy->reveal();
        $input->airport = $this->prophesize(Airport::class)->reveal();
        $input->salesOrganisationService = $this->prophesize(Location::class)->reveal();
        $input->hourMeter = 999;
        $input->nestedCustomerServiceRecord = null;

        $clone = $factory->duplicate($original, $input);

        self::assertNotSame($original, $clone);

        self::assertFalse($clone->confidential);
        self::assertNull($clone->serialNumber);

        self::assertSame($input->equipmentRecord, $clone->equipmentRecord);
        self::assertSame($input->airport, $clone->airport);
        self::assertSame($input->salesOrganisationService, $clone->salesOrganisationService);
        self::assertSame(TechnicianOnCall::IN_PROGRESS, $clone->status);
        self::assertNull($clone->createdBy);
        self::assertSame(999, $clone->hourMeter);
        self::assertFalse($clone->factoryFlag);
        self::assertSame($endUser, $clone->customer);
    }
}
