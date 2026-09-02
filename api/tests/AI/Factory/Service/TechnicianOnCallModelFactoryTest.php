<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Service;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Service\TechnicianOnCallModelFactory;
use App\AI\Factory\Support\EquipmentRecordModelFactory;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Service\ServiceActivity;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCall\TechnicianOnCallSurvey;
use App\Entity\Service\TechnicianOnCallTag;
use App\Entity\Service\TechnicianOnCallType;
use App\Entity\Support\UnitOperationalStatus;
use PHPUnit\Framework\TestCase;

final class TechnicianOnCallModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(TechnicianOnCall::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $model = $this->makeFactory()->create($this->makeEntity());

        self::assertSame(0, $model->id);
        self::assertSame(TechnicianOnCall::PENDING, $model->status);
        self::assertSame('short desc', $model->title);
        self::assertSame('full description', $model->description);
        self::assertSame('Troubleshooting', $model->activityType);
        self::assertSame('IF 10', $model->indiceFactor);
        self::assertFalse($model->factoryFlag);
        self::assertFalse($model->confidential);
        self::assertNull($model->solvedAt);
        self::assertNull($model->createdBy);
        self::assertNull($model->technician);
        self::assertNull($model->assignee);
        self::assertNull($model->mainContact);
        self::assertNull($model->customer);
        self::assertNull($model->equipmentRecord);
        self::assertNull($model->serviceOrganizationLocation);
        self::assertNull($model->airport);
        self::assertNull($model->unitOperationalStatus);
        self::assertNull($model->type);
        self::assertNull($model->errorCodes);
        self::assertNull($model->survey);
        self::assertSame([], $model->tags);
    }

    public function testCreateMapsScalarDetails(): void
    {
        $entity = $this->makeEntity();
        $entity->status = TechnicianOnCall::SOLVED;
        $entity->solvedAt = new \DateTime('2024-06-15 10:00:00');
        $entity->indiceFactor = 'IF 100';
        $entity->errorCodes = 'E001,E002';
        $entity->symptoms = 'noise';
        $entity->rootCause = 'worn bearing';
        $entity->solution = 'replaced bearing';
        $entity->thirdPartyName = 'ACME';
        $entity->thirdPartyRef = 'PO-42';
        $entity->thirdPartyHours = 5;
        $entity->thirdPartyJobDescription = 'external repair';
        $entity->factoryFlag = true;
        $entity->confidential = true;
        $entity->warrantyLegacyId = 77;
        $entity->hourMeter = 1234;
        $entity->serialNumber = 'SN-1';
        $entity->technicianOnCallType = $this->makeType('Warranty');
        $entity->unitOperationalStatus = $this->makeUnitOperationalStatus('AOG');

        $model = $this->makeFactory()->create($entity);

        self::assertSame(TechnicianOnCall::SOLVED, $model->status);
        self::assertSame('IF 100', $model->indiceFactor);
        self::assertSame('E001,E002', $model->errorCodes);
        self::assertSame('noise', $model->symptoms);
        self::assertSame('worn bearing', $model->rootCause);
        self::assertSame('replaced bearing', $model->solution);
        self::assertSame('ACME', $model->thirdPartyName);
        self::assertSame('PO-42', $model->thirdPartyRef);
        self::assertSame(5, $model->thirdPartyHours);
        self::assertSame('external repair', $model->thirdPartyJobDescription);
        self::assertTrue($model->factoryFlag);
        self::assertTrue($model->confidential);
        self::assertSame(77, $model->warrantyLegacyId);
        self::assertSame(1234, $model->hourMeter);
        self::assertSame('SN-1', $model->serialNumber);
        self::assertSame('Warranty', $model->type);
        self::assertSame('AOG', $model->unitOperationalStatus);
    }

    public function testCreateMapsPeopleRelations(): void
    {
        $entity = $this->makeEntity();
        $entity->createdBy = $this->makePeople('Alice');
        $entity->technician = $this->makePeople('Bob');
        $entity->assignee = $this->makePeople('Carol');

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->createdBy);
        self::assertSame('Alice', $model->createdBy->firstname);
        self::assertNotNull($model->technician);
        self::assertSame('Bob', $model->technician->firstname);
        self::assertNotNull($model->assignee);
        self::assertSame('Carol', $model->assignee->firstname);
    }

    public function testCreateMapsCustomerAndLocation(): void
    {
        $customer = (new Customer())->setName('Air France')->setStatus('active');

        $location = new Location();
        $location->setName('TLD ASI');
        $location->setErp(0);

        $entity = $this->makeEntity();
        $entity->customer = $customer;
        $entity->salesOrganisationService = $location;

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->customer);
        self::assertSame('Air France', $model->customer->name);
        self::assertSame('active', $model->customer->status);
        self::assertNotNull($model->serviceOrganizationLocation);
        self::assertSame('TLD ASI', $model->serviceOrganizationLocation->name);
    }

    public function testCreateMapsSurvey(): void
    {
        $survey = new TechnicianOnCallSurvey();
        $survey->execution = 5;
        $survey->responsiveness = 4;
        $survey->communication = 3;
        $survey->attitude = 5;
        $survey->comment = 'Great service';

        $entity = $this->makeEntity();
        $entity->survey = $survey;

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->survey);
        self::assertSame(5, $model->survey->execution);
        self::assertSame(4, $model->survey->responsiveness);
        self::assertSame(3, $model->survey->communication);
        self::assertSame(5, $model->survey->attitude);
        self::assertSame('Great service', $model->survey->comment);
    }

    public function testCreateMapsTags(): void
    {
        $entity = $this->makeEntity();
        $entity->addTag((new TechnicianOnCallTag())->setName('urgent'));
        $entity->addTag((new TechnicianOnCallTag())->setName('recurring'));

        $model = $this->makeFactory()->create($entity);

        self::assertSame(['urgent', 'recurring'], $model->tags);
    }

    public function testCreateDelegatesEquipmentRecordToItsFactory(): void
    {
        $equipmentRecord = $this->createMock(EquipmentRecord::class);
        $equipmentRecord->method('getId')->willReturn(1);
        $equipmentRecord->method('getLegacyId')->willReturn(1);
        $equipmentRecord->method('getSerialNumber')->willReturn('SN-4242');
        $equipmentRecord->method('getModel')->willReturn('TBL-180');
        $equipmentRecord->method('getType')->willReturn('Belt Loader');

        $entity = $this->makeEntity();
        $entity->equipmentRecord = $equipmentRecord;

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->equipmentRecord);
        self::assertSame('SN-4242', $model->equipmentRecord->serialNumber);
        self::assertSame('TBL-180', $model->equipmentRecord->model);
        self::assertSame('Belt Loader', $model->equipmentRecord->type);
    }

    private function makeFactory(): TechnicianOnCallModelFactory
    {
        return new TechnicianOnCallModelFactory(
            new UserModelFactory(),
            new LocationModelFactory(),
            new EquipmentRecordModelFactory(new LocationModelFactory()),
        );
    }

    private function makeEntity(): TechnicianOnCall
    {
        $entity = new TechnicianOnCall();
        $entity->createdAt = new \DateTime('2024-01-01 00:00:00');
        $entity->title = 'short desc';
        $entity->description = 'full description';
        $entity->serviceActivity = $this->makeServiceActivity('Troubleshooting');

        return $entity;
    }

    private function makePeople(string $firstname): People
    {
        $people = new People();
        $people->setFirstname($firstname);
        $people->setLastname('X');
        $people->setUsername(mb_strtolower($firstname));
        $people->setEmail(mb_strtolower($firstname).'@x.test');

        return $people;
    }

    private function makeServiceActivity(string $name): ServiceActivity
    {
        $serviceActivity = new ServiceActivity();
        $serviceActivity->name = $name;
        $serviceActivity->description = $name;

        return $serviceActivity;
    }

    private function makeType(string $name): TechnicianOnCallType
    {
        $type = new TechnicianOnCallType();
        $type->name = $name;

        return $type;
    }

    private function makeUnitOperationalStatus(string $name): UnitOperationalStatus
    {
        $status = new UnitOperationalStatus();
        $status->setName($name);

        return $status;
    }
}
