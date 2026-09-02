<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Materials;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Materials\IntercoShippingRecordModelFactory;
use Doctrine\Common\Collections\ArrayCollection;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;
use LegacyBundle\Entity\Materials\IntercoShippingRecord;
use LegacyBundle\Entity\Materials\IntercoShippingRecordLine;
use PHPUnit\Framework\TestCase;

final class IntercoShippingRecordModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(IntercoShippingRecord::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = $this->makeEntity();
        $entity->trackingNumber = 'TRK-123';
        $entity->notes = 'shipping notes';

        $model = $this->makeFactory()->create($entity);

        self::assertSame('TRK-123', $model->trackingNumber);
        self::assertSame('shipping notes', $model->notes);
        self::assertNull($model->poster);
        self::assertNull($model->fromBusinessUnit);
        self::assertNull($model->toBusinessUnit);
        self::assertSame([], $model->lines);
    }

    public function testCreateMapsRelationsAndLines(): void
    {
        $entity = $this->makeEntity();
        $entity->poster = $this->makePeople('Alice');
        $entity->fromBusinessUnit = $this->makeLocation('LYON');
        $entity->toBusinessUnit = $this->makeLocation('PARIS');

        $line = new IntercoShippingRecordLine();
        $line->packingSlipNumber = 'PSN-1';
        $entity->lines = new ArrayCollection([$line]);

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->poster);
        self::assertSame('Alice', $model->poster->firstname);
        self::assertNotNull($model->fromBusinessUnit);
        self::assertSame('LYON', $model->fromBusinessUnit->name);
        self::assertNotNull($model->toBusinessUnit);
        self::assertSame('PARIS', $model->toBusinessUnit->name);
        self::assertCount(1, $model->lines);
        self::assertSame('PSN-1', $model->lines[0]->packingSlipNumber);
    }

    private function makeFactory(): IntercoShippingRecordModelFactory
    {
        return new IntercoShippingRecordModelFactory(new UserModelFactory(), new LocationModelFactory());
    }

    private function makeEntity(): IntercoShippingRecord
    {
        $entity = new IntercoShippingRecord();
        $entity->openedAt = new \DateTimeImmutable('2025-01-01');
        $entity->status = '';
        $entity->erpCustomerNumber = '';
        $entity->transportationType = '';
        $entity->containerNumber = '';
        $entity->trackingNumber = '';
        $entity->notes = '';
        $entity->lines = new ArrayCollection();

        return $entity;
    }

    private function makeLocation(string $name): LocationById
    {
        $location = new LocationById();
        $location->name = $name;
        $location->erp = 0;

        return $location;
    }

    private function makePeople(string $firstname): PeopleById
    {
        $people = new PeopleById();
        $people->firstname = $firstname;
        $people->lastname = 'X';
        $people->username = mb_strtolower($firstname);
        $people->email = mb_strtolower($firstname).'@x.test';

        return $people;
    }
}
