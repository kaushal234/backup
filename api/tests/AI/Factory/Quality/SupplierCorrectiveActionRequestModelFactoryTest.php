<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Quality;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Quality\SupplierCorrectiveActionRequestModelFactory;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Parts\SupplierCorrectiveActionRequestPart;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use PHPUnit\Framework\TestCase;

final class SupplierCorrectiveActionRequestModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(SupplierCorrectiveActionRequest::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = $this->makeEntity();
        $entity->shortDescription = 'scar short';
        $entity->description = 'scar description';

        $model = $this->makeFactory()->create($entity);

        self::assertSame('scar short', $model->shortDescription);
        self::assertSame('scar description', $model->description);
        self::assertSame('Acme', $model->supplierName);
        self::assertSame('SUP-1', $model->supplierNumber);
        self::assertSame('LYON', $model->factory->name);
        self::assertNull($model->representative);
        self::assertNull($model->leader);
        self::assertSame([], $model->parts);
    }

    public function testCreateAllowsNullableScalars(): void
    {
        $entity = $this->makeEntity();
        $entity->issueOrigin = null;
        $entity->correctiveAction = null;
        $entity->commercialAgreement = null;
        $entity->verificationDescription = null;
        $entity->preventiveAction = null;
        $entity->conclusion = null;

        $model = $this->makeFactory()->create($entity);

        self::assertNull($model->issueOrigin);
        self::assertNull($model->correctiveAction);
        self::assertNull($model->commercialAgreement);
        self::assertNull($model->verificationDescription);
        self::assertNull($model->preventiveAction);
        self::assertNull($model->conclusion);
    }

    public function testCreateMapsRelationsAndParts(): void
    {
        $representative = $this->createMock(People::class);
        $representative->method('getUsername')->willReturn('alice');
        $representative->method('getEmail')->willReturn('alice@x.test');
        $representative->method('getFirstname')->willReturn('Alice');
        $representative->method('getLastname')->willReturn('X');

        $leader = $this->createMock(People::class);
        $leader->method('getUsername')->willReturn('bob');
        $leader->method('getEmail')->willReturn('bob@x.test');
        $leader->method('getFirstname')->willReturn('Bob');
        $leader->method('getLastname')->willReturn('Y');

        $part = new SupplierCorrectiveActionRequestPart();
        $part->createdAt = new \DateTime('2025-01-01');
        $part->partNumber = 'PN-1';
        $part->description = 'a part';
        $part->quantity = 3;
        $part->unitOfMeasure = 'EA';

        $entity = $this->makeEntity();
        $entity->representative = $representative;
        $entity->leader = $leader;
        $entity->addPart($part);

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->representative);
        self::assertSame('alice', $model->representative->username);
        self::assertNotNull($model->leader);
        self::assertSame('bob', $model->leader->username);
        self::assertCount(1, $model->parts);
        self::assertSame('PN-1', $model->parts[0]->partNumber);
        self::assertSame(3.0, $model->parts[0]->quantity);
    }

    private function makeFactory(): SupplierCorrectiveActionRequestModelFactory
    {
        return new SupplierCorrectiveActionRequestModelFactory(new UserModelFactory(), new LocationModelFactory());
    }

    private function makeEntity(): SupplierCorrectiveActionRequest
    {
        $location = $this->createMock(Location::class);
        $location->method('getName')->willReturn('LYON');
        $location->method('getErp')->willReturn(1);

        $entity = new SupplierCorrectiveActionRequest();
        $entity->createdAt = new \DateTime('2025-01-01');
        $entity->iFactor = 'A';
        $entity->shortDescription = '';
        $entity->description = '';
        $entity->factory = $location;
        $entity->setSupplierName('Acme');
        $entity->setSupplierNumber('SUP-1');

        return $entity;
    }
}
