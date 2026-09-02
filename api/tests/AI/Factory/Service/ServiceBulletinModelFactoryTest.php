<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Service;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Service\ServiceBulletinModelFactory;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;
use LegacyBundle\Entity\ServiceBulletin;
use PHPUnit\Framework\TestCase;

final class ServiceBulletinModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(ServiceBulletin::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = $this->makeEntity();
        $entity->status = 'OPEN';
        $entity->category = 'CAT';
        $entity->title = 'Title';
        $entity->type = 'TYPE';
        $entity->description = 'description';
        $entity->confidential = 'NO';
        $entity->createdAt = new \DateTimeImmutable('2025-01-15');

        $model = $this->makeFactory()->create($entity);

        self::assertSame('OPEN', $model->status);
        self::assertSame('CAT', $model->category);
        self::assertSame('Title', $model->title);
        self::assertSame('TYPE', $model->type);
        self::assertSame('description', $model->description);
        self::assertSame('NO', $model->confidential);
        self::assertSame('2025-01-15', $model->createdAt->format('Y-m-d'));
        self::assertNull($model->poster);
        self::assertNull($model->factory);
    }

    public function testCreateMapsRelations(): void
    {
        $entity = $this->makeEntity();
        $entity->createdAt = new \DateTimeImmutable('2025-01-15');

        $poster = new PeopleById();
        $poster->firstname = 'Alice';
        $poster->lastname = 'X';
        $poster->username = 'alice';
        $poster->email = 'alice@x.test';
        $entity->poster = $poster;

        $factory = new LocationById();
        $factory->name = 'PARIS';
        $factory->erp = 1;
        $entity->factory = $factory;

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->poster);
        self::assertSame('Alice', $model->poster->firstname);
        self::assertNotNull($model->factory);
        self::assertSame('PARIS', $model->factory->name);
    }

    private function makeFactory(): ServiceBulletinModelFactory
    {
        return new ServiceBulletinModelFactory(new UserModelFactory(), new LocationModelFactory());
    }

    private function makeEntity(): ServiceBulletin
    {
        $entity = new ServiceBulletin();
        $entity->parentId = 0;
        $entity->status = '';
        $entity->category = '';
        $entity->title = '';
        $entity->type = '';
        $entity->description = '';
        $entity->confidential = '';
        $entity->importanceFactor = 0;
        $entity->laborHours = 0;
        $entity->numberOfTechniciansNeeded = 0;
        $entity->factoryPartAvailabilityStatus = '';
        $entity->createdAt = new \DateTimeImmutable();

        return $entity;
    }
}
