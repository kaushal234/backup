<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Sales;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Sales\EquipmentShippingRecordModelFactory;
use App\AI\Factory\Support\EquipmentRecordModelFactory;
use App\Entity\Directory\Location;
use App\Entity\FreightForwarder;
use App\Entity\Sales\Customer;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\Incoterm;
use PHPUnit\Framework\TestCase;

final class EquipmentShippingRecordModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = new EquipmentShippingRecordModelFactory(new EquipmentRecordModelFactory(new LocationModelFactory()));

        self::assertTrue($factory->supports(EquipmentShippingRecord::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = $this->makeEntity();
        $entity->modality = EquipmentShippingRecord::AIR;
        $entity->shipAuthorization = true;
        $entity->notes = 'some notes';

        $model = (new EquipmentShippingRecordModelFactory(new EquipmentRecordModelFactory(new LocationModelFactory())))->create($entity);

        self::assertSame(EquipmentShippingRecord::AIR, $model->modality);
        self::assertTrue($model->shipAuthorization);
        self::assertSame('some notes', $model->notes);
        self::assertSame('LYON', $model->sso->name);
        self::assertSame('EXW', $model->incoterm->code);
        self::assertNull($model->customer);
        self::assertNull($model->forwarder);
        self::assertNull($model->carrier);
        self::assertSame([], $model->lines);
        self::assertSame([], $model->costs);
    }

    public function testCreateAllowsNullableOptionalScalars(): void
    {
        $entity = $this->makeEntity();
        $entity->loadingPlace = null;
        $entity->departurePlace = null;
        $entity->arrivalPlace = null;
        $entity->notes = null;

        $model = (new EquipmentShippingRecordModelFactory(new EquipmentRecordModelFactory(new LocationModelFactory())))->create($entity);

        self::assertNull($model->loadingPlace);
        self::assertNull($model->departurePlace);
        self::assertNull($model->arrivalPlace);
        self::assertNull($model->notes);
    }

    public function testCreateMapsCustomerAndForwarders(): void
    {
        $customer = $this->createMock(Customer::class);
        $customer->method('getName')->willReturn('Acme');
        $customer->method('getStatus')->willReturn('ACTIVE');

        $forwarder = $this->createMock(FreightForwarder::class);
        $forwarder->method('getName')->willReturn('Forwarder');

        $carrier = $this->createMock(FreightForwarder::class);
        $carrier->method('getName')->willReturn('Carrier');

        $entity = $this->makeEntity();
        $entity->customer = $customer;
        $entity->forwarder = $forwarder;
        $entity->carrier = $carrier;

        $model = (new EquipmentShippingRecordModelFactory(new EquipmentRecordModelFactory(new LocationModelFactory())))->create($entity);

        self::assertNotNull($model->customer);
        self::assertSame('Acme', $model->customer->name);
        self::assertNotNull($model->forwarder);
        self::assertSame('Forwarder', $model->forwarder->name);
        self::assertNotNull($model->carrier);
        self::assertSame('Carrier', $model->carrier->name);
    }

    private function makeEntity(): EquipmentShippingRecord
    {
        $sso = $this->createMock(Location::class);
        $sso->method('getName')->willReturn('LYON');
        $sso->method('getErp')->willReturn(1);

        $incoterm = new Incoterm();
        $incoterm->code = 'EXW';
        $incoterm->description = '';

        $entity = new EquipmentShippingRecord();
        $entity->sso = $sso;
        $entity->incoterm = $incoterm;
        $entity->createdAt = new \DateTime('2025-01-01');

        return $entity;
    }
}
