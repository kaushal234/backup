<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Quality;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Quality\CorrectivePreventiveActionModelFactory;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;
use LegacyBundle\Entity\Quality\CorrectivePreventiveAction;
use PHPUnit\Framework\TestCase;

final class CorrectivePreventiveActionModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(CorrectivePreventiveAction::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = new CorrectivePreventiveAction();
        $entity->shortDescription = 'cpa short';
        $entity->description = 'cpa description';

        $model = $this->makeFactory()->create($entity);

        self::assertSame('cpa short', $model->shortDescription);
        self::assertSame('cpa description', $model->description);
        self::assertNull($model->location);
        self::assertNull($model->projectLeader);
        self::assertNull($model->poster);
        self::assertNull($model->initiator);
    }

    public function testCreateMapsRelations(): void
    {
        $entity = new CorrectivePreventiveAction();
        $entity->location = $this->makeLocation('LYON');
        $entity->projectLeader = $this->makePeople('Alice');
        $entity->poster = $this->makePeople('Bob');
        $entity->initiator = $this->makePeople('Carol');
        $entity->openedDate = new \DateTime('2025-01-10');

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->location);
        self::assertSame('LYON', $model->location->name);
        self::assertNotNull($model->projectLeader);
        self::assertSame('Alice', $model->projectLeader->firstname);
        self::assertNotNull($model->poster);
        self::assertSame('Bob', $model->poster->firstname);
        self::assertNotNull($model->initiator);
        self::assertSame('Carol', $model->initiator->firstname);
        self::assertSame('2025-01-10', $model->openedDate?->format('Y-m-d'));
    }

    private function makeFactory(): CorrectivePreventiveActionModelFactory
    {
        return new CorrectivePreventiveActionModelFactory(new UserModelFactory(), new LocationModelFactory());
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
