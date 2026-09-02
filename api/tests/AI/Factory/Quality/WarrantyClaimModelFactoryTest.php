<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Quality;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Quality\WarrantyClaimModelFactory;
use Doctrine\Common\Collections\ArrayCollection;
use LegacyBundle\Entity\Directory\LocationByName;
use LegacyBundle\Entity\Directory\PeopleByUsername;
use LegacyBundle\Entity\Quality\WarrantyClaim;
use LegacyBundle\Entity\Quality\WarrantyClaimPart;
use PHPUnit\Framework\TestCase;

final class WarrantyClaimModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(WarrantyClaim::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = $this->makeEntity();
        $entity->details = 'warranty details';
        $entity->description = 'problem description';

        $model = $this->makeFactory()->create($entity);

        self::assertSame('warranty details', $model->details);
        self::assertSame('problem description', $model->description);
        self::assertNull($model->manufacturingLocation);
        self::assertNull($model->salesOrganization);
        self::assertNull($model->enteredBy);
        self::assertNull($model->productionManagerAcceptUser);
        self::assertNull($model->technician);
        self::assertSame([], $model->parts);
    }

    public function testCreateMapsRelationsAndParts(): void
    {
        $entity = $this->makeEntity();
        $entity->manufacturingLocation = $this->makeLocation('LYON');
        $entity->salesOrganization = $this->makeLocation('PARIS');
        $entity->enteredBy = $this->makePeople('Alice');
        $entity->technician = 'Bob';

        $part = new WarrantyClaimPart();
        $part->partNumber = 'PN-1';
        $part->partDescription = 'a part';
        $part->quantity = '2';
        $entity->parts = new ArrayCollection([$part]);

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->manufacturingLocation);
        self::assertSame('LYON', $model->manufacturingLocation->name);
        self::assertNotNull($model->salesOrganization);
        self::assertSame('PARIS', $model->salesOrganization->name);
        self::assertNotNull($model->enteredBy);
        self::assertSame('Alice', $model->enteredBy->firstname);
        self::assertSame('Bob', $model->technician);
        self::assertCount(1, $model->parts);
        self::assertSame('PN-1', $model->parts[0]->partNumber);
        self::assertSame('2', $model->parts[0]->quantity);
    }

    public function testCreateAllowsNullableScalars(): void
    {
        $entity = $this->makeEntity();
        $entity->details = null;
        $entity->description = null;

        $model = $this->makeFactory()->create($entity);

        self::assertNull($model->details);
        self::assertNull($model->description);
    }

    private function makeFactory(): WarrantyClaimModelFactory
    {
        return new WarrantyClaimModelFactory(new UserModelFactory(), new LocationModelFactory());
    }

    private function makeEntity(): WarrantyClaim
    {
        $entity = new WarrantyClaim();
        $entity->parts = new ArrayCollection();

        return $entity;
    }

    private function makeLocation(string $name): LocationByName
    {
        $location = new LocationByName();
        $location->name = $name;
        $location->erp = 0;

        return $location;
    }

    private function makePeople(string $firstname): PeopleByUsername
    {
        $people = new PeopleByUsername();
        $people->firstname = $firstname;
        $people->lastname = 'X';
        $people->username = mb_strtolower($firstname);
        $people->email = mb_strtolower($firstname).'@x.test';

        return $people;
    }
}
