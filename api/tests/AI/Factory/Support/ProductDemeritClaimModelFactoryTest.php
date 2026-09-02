<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Support;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Support\ProductDemeritClaimModelFactory;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;
use LegacyBundle\Entity\Support\ProductDemeritClaim;
use PHPUnit\Framework\TestCase;

final class ProductDemeritClaimModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(ProductDemeritClaim::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = $this->makeEntity();
        $entity->shortDescription = 'demerit short';
        $entity->description = 'demerit description';

        $model = $this->makeFactory()->create($entity);

        self::assertSame('demerit short', $model->shortDescription);
        self::assertSame('demerit description', $model->description);
        self::assertNull($model->factory);
        self::assertNull($model->poster);
        self::assertNull($model->initiator);
        self::assertNull($model->assignee);
    }

    public function testCreateMapsRelations(): void
    {
        $entity = $this->makeEntity();
        $entity->factory = $this->makeLocation('LYON');
        $entity->poster = $this->makePeople('Alice');
        $entity->initiator = $this->makePeople('Bob');
        $entity->assignee = $this->makePeople('Carol');

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->factory);
        self::assertSame('LYON', $model->factory->name);
        self::assertNotNull($model->poster);
        self::assertSame('Alice', $model->poster->firstname);
        self::assertNotNull($model->initiator);
        self::assertSame('Bob', $model->initiator->firstname);
        self::assertNotNull($model->assignee);
        self::assertSame('Carol', $model->assignee->firstname);
    }

    public function testCreateCastsNullableBoolFlagsToBool(): void
    {
        $entity = $this->makeEntity();
        $entity->involvesIbs = true;
        $entity->involvesIhs = false;
        $entity->involvesLink = null;
        $entity->readyToClose = null;

        $model = $this->makeFactory()->create($entity);

        self::assertTrue($model->involvesIbs);
        self::assertFalse($model->involvesIhs);
        self::assertFalse($model->involvesLink);
        self::assertFalse($model->readyToClose);
    }

    private function makeFactory(): ProductDemeritClaimModelFactory
    {
        return new ProductDemeritClaimModelFactory(new UserModelFactory(), new LocationModelFactory());
    }

    private function makeEntity(): ProductDemeritClaim
    {
        $entity = new ProductDemeritClaim();
        $entity->shortDescription = '';
        $entity->description = '';

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
