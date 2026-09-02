<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Service;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Service\CustomerServiceRecordModelFactory;
use App\AI\Factory\Support\EquipmentRecordModelFactory;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\ServiceBulletinCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\TechnicianOnCallCustomerServiceRecord;
use PHPUnit\Framework\TestCase;

final class CustomerServiceRecordModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(AbstractCustomerServiceRecord::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = $this->makeEntity(CustomerServiceRecord::class);
        $entity->title = 'a title';
        $entity->description = 'a desc';

        $model = $this->makeFactory()->create($entity);

        self::assertSame('default', $model->type);
        self::assertSame('a title', $model->title);
        self::assertSame('a desc', $model->description);
        self::assertNull($model->createdBy);
        self::assertNull($model->airport);
        self::assertSame([], $model->interventions);
        self::assertSame('SN-1', $model->equipmentRecord->serialNumber);
    }

    public function testCreateAllowsNullableScalars(): void
    {
        $entity = $this->makeEntity(CustomerServiceRecord::class);
        $entity->description = null;
        $entity->updatedAt = null;
        $entity->plannedAt = null;
        $entity->completedAt = null;
        $entity->closedAt = null;

        $model = $this->makeFactory()->create($entity);

        self::assertNull($model->description);
        self::assertNull($model->updatedAt);
        self::assertNull($model->plannedAt);
        self::assertNull($model->completedAt);
        self::assertNull($model->closedAt);
    }

    /**
     * @return iterable<string, array{class-string<AbstractCustomerServiceRecord>, string}>
     */
    public static function typeProvider(): iterable
    {
        yield 'toc' => [TechnicianOnCallCustomerServiceRecord::class, 'toc'];
        yield 'service_bulletin' => [ServiceBulletinCustomerServiceRecord::class, 'service_bulletin'];
        yield 'commissioning' => [CommissioningCustomerServiceRecord::class, 'commissioning'];
        yield 'default' => [CustomerServiceRecord::class, 'default'];
    }

    /**
     * @dataProvider typeProvider
     *
     * @param class-string<AbstractCustomerServiceRecord> $class
     */
    public function testResolveType(string $class, string $expected): void
    {
        $model = $this->makeFactory()->create($this->makeEntity($class));

        self::assertSame($expected, $model->type);
    }

    private function makeFactory(): CustomerServiceRecordModelFactory
    {
        return new CustomerServiceRecordModelFactory(
            new UserModelFactory(),
            new EquipmentRecordModelFactory(new LocationModelFactory()),
        );
    }

    /**
     * @param class-string<AbstractCustomerServiceRecord> $class
     */
    private function makeEntity(string $class): AbstractCustomerServiceRecord
    {
        $record = $this->createMock(EquipmentRecord::class);
        $record->method('getLegacyId')->willReturn(1);
        $record->method('getSerialNumber')->willReturn('SN-1');
        $record->method('getModel')->willReturn('M');
        $record->method('getType')->willReturn('T');

        $entity = new $class();
        $entity->createdAt = new \DateTime('2025-01-01');
        $entity->description = '';
        $entity->equipmentRecord = $record;
        $entity->createdBy = null;
        $entity->leader = $this->createPeopleMock();

        return $entity;
    }

    private function createPeopleMock(): People
    {
        $people = $this->createMock(People::class);
        $people->method('getUsername')->willReturn('alice');
        $people->method('getEmail')->willReturn('alice@x.test');
        $people->method('getFirstname')->willReturn('Alice');
        $people->method('getLastname')->willReturn('X');

        return $people;
    }
}
