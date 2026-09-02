<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Support;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Support\EquipmentRecordModelFactory;
use App\AI\Factory\Support\ManualModelFactory;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Support\EquipmentSerial;
use App\Entity\Support\Manual;
use PHPUnit\Framework\TestCase;

final class ManualModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = new ManualModelFactory(new EquipmentRecordModelFactory(new LocationModelFactory()));

        self::assertTrue($factory->supports(Manual::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = new Manual();
        $entity->description = 'manual desc';
        $entity->features = 'features';
        $entity->language = 'en';
        $entity->status = 'RELEASED';
        $entity->createdAt = new \DateTime('2025-01-01');
        $entity->createdBy = null;

        $model = (new ManualModelFactory(new EquipmentRecordModelFactory(new LocationModelFactory())))->create($entity);

        self::assertSame('manual desc', $model->description);
        self::assertSame('features', $model->features);
        self::assertSame('en', $model->language);
        self::assertSame('RELEASED', $model->status);
        self::assertSame('2025-01-01', $model->createdAt?->format('Y-m-d'));
        self::assertNull($model->createdBy);
        self::assertNull($model->equipmentSerial);
        self::assertNull($model->equipmentRecord);
    }

    public function testCreateLeavesAllOptionalsAsNull(): void
    {
        $entity = new Manual();
        $entity->createdBy = null;

        $model = (new ManualModelFactory(new EquipmentRecordModelFactory(new LocationModelFactory())))->create($entity);

        self::assertNull($model->description);
        self::assertNull($model->features);
        self::assertNull($model->language);
        self::assertNull($model->status);
        self::assertNull($model->createdAt);
        self::assertNull($model->createdBy);
    }

    public function testCreateMapsRelations(): void
    {
        $createdBy = $this->createMock(People::class);
        $createdBy->method('getUsername')->willReturn('alice');
        $createdBy->method('getEmail')->willReturn('alice@x.test');
        $createdBy->method('getFirstname')->willReturn('Alice');
        $createdBy->method('getLastname')->willReturn('X');

        $serial = new EquipmentSerial();
        $serial->model = 'TPX-200';
        $serial->serial = 'SER-1';
        $serial->brand = 'TLD';

        $record = $this->createMock(EquipmentRecord::class);
        $record->method('getLegacyId')->willReturn(1);
        $record->method('getSerialNumber')->willReturn('SN-1');
        $record->method('getModel')->willReturn('MODEL-X');
        $record->method('getType')->willReturn('TYPE-Y');

        $entity = new Manual();
        $entity->createdBy = $createdBy;
        $entity->equipmentSerial = $serial;
        $entity->equipmentRecord = $record;

        $model = (new ManualModelFactory(new EquipmentRecordModelFactory(new LocationModelFactory())))->create($entity);

        self::assertNotNull($model->createdBy);
        self::assertSame('alice', $model->createdBy->username);
        self::assertNotNull($model->equipmentSerial);
        self::assertSame('TPX-200', $model->equipmentSerial->model);
        self::assertSame('SER-1', $model->equipmentSerial->serial);
        self::assertSame('TLD', $model->equipmentSerial->brand);
        self::assertNotNull($model->equipmentRecord);
        self::assertSame('SN-1', $model->equipmentRecord->serialNumber);
        self::assertSame('MODEL-X', $model->equipmentRecord->model);
        self::assertSame('TYPE-Y', $model->equipmentRecord->type);
    }
}
