<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Purchasing;

use App\AI\Dto\Purchasing\VendorWarrantyClaim\NCRVendorWarrantyClaimModel;
use App\AI\Dto\Purchasing\VendorWarrantyClaim\WCVendorWarrantyClaimModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Purchasing\VendorWarrantyClaimModelFactory;
use App\AI\Factory\Quality\NonConformityModelFactory;
use App\AI\Factory\Quality\SupplierCorrectiveActionRequestModelFactory;
use App\AI\Factory\Support\EquipmentRecordModelFactory;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Parts\VendorWarrantyClaimPart;
use App\Entity\Purchasing\NCRVendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Entity\Purchasing\VendorWarrantyClaimType;
use App\Entity\Purchasing\WCVendorWarrantyClaim;
use App\Entity\Quality\NonConformity;
use PHPUnit\Framework\TestCase;

final class VendorWarrantyClaimModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(VendorWarrantyClaim::class));
        self::assertFalse($factory->supports(NCRVendorWarrantyClaim::class));
        self::assertFalse($factory->supports(WCVendorWarrantyClaim::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateNCRReturnsNCRModelWithNonConformity(): void
    {
        $entity = $this->makeNCREntity();
        $entity->requestedSupplierAction = 'replace the part';
        $entity->setSupplierName('ACME');

        $part = new VendorWarrantyClaimPart();
        $part->partNumber = 'P-001';
        $part->description = 'A part';
        $part->quantity = 2.0;
        $part->ship = true;
        $entity->addPart($part);

        $model = $this->makeFactory()->create($entity);

        self::assertInstanceOf(NCRVendorWarrantyClaimModel::class, $model);
        self::assertSame('replace the part', $model->requestedSupplierAction);
        self::assertSame('ACME', $model->supplierName);
        self::assertSame('CLAIM', $model->type->name);
        self::assertSame('LYON', $model->location->name);
        self::assertSame('alice', $model->poster->username);
        self::assertCount(1, $model->parts);
        self::assertSame('P-001', $model->parts[0]->partNumber);
        self::assertSame('NC problem', $model->nonConformity->problem);
    }

    public function testCreateWCReturnsWCModelWithWarrantyClaimId(): void
    {
        $entity = $this->makeWCEntity();
        $entity->warrantyClaimId = 4242;

        $model = $this->makeFactory()->create($entity);

        self::assertInstanceOf(WCVendorWarrantyClaimModel::class, $model);
        self::assertSame(4242, $model->warrantyClaimId);
        self::assertSame('CLAIM', $model->type->name);
        self::assertSame('PENDING', $model->status->name);
    }

    private function makeFactory(): VendorWarrantyClaimModelFactory
    {
        $peopleFactory = new UserModelFactory();
        $locationFactory = new LocationModelFactory();

        return new VendorWarrantyClaimModelFactory(
            $peopleFactory,
            $locationFactory,
            new SupplierCorrectiveActionRequestModelFactory($peopleFactory, $locationFactory),
            new NonConformityModelFactory($peopleFactory, $locationFactory, new EquipmentRecordModelFactory($locationFactory)),
        );
    }

    private function makeNCREntity(): NCRVendorWarrantyClaim
    {
        $entity = new NCRVendorWarrantyClaim();
        $this->fillCommon($entity);
        $entity->nonConformity = $this->makeNonConformity();

        return $entity;
    }

    private function makeWCEntity(): WCVendorWarrantyClaim
    {
        $entity = new WCVendorWarrantyClaim();
        $this->fillCommon($entity);
        $entity->warrantyClaimId = 0;

        return $entity;
    }

    private function fillCommon(VendorWarrantyClaim $entity): void
    {
        $entity->type = $this->makeType('CLAIM');
        $entity->status = $this->makeStatus('PENDING');
        $entity->location = $this->makeLocation('LYON');
        $entity->poster = $this->makePeople('alice');
        $entity->assignee = null;
        $entity->createdAt = new \DateTimeImmutable('2025-01-01');
        $entity->requestedSupplierAction = '';
    }

    private function makeNonConformity(): NonConformity
    {
        $nc = new NonConformity();
        $nc->location = $this->makeLocation('LYON');
        $nc->createdAt = new \DateTime('2025-01-01');
        $nc->problem = 'NC problem';
        $nc->shortDescription = 'NC short';
        $nc->iFactor = 'A';
        $nc->hours = null;
        $nc->reportedBy = null;

        return $nc;
    }

    private function makeType(string $name): VendorWarrantyClaimType
    {
        $type = new VendorWarrantyClaimType();
        $type->name = $name;
        $type->description = $name.' description';

        return $type;
    }

    private function makeStatus(string $name): VendorWarrantyClaimStatus
    {
        $status = new VendorWarrantyClaimStatus();
        $status->name = $name;
        $status->description = $name.' description';
        $status->position = 0;

        return $status;
    }

    private function makeLocation(string $name): Location
    {
        $location = new Location();
        $location->setName($name);
        $location->setErp(0);

        return $location;
    }

    private function makePeople(string $username): People
    {
        $people = new People();
        $people->setUsername($username);
        $people->setEmail($username.'@x.test');
        $people->setFirstname($username);
        $people->setLastname('X');

        return $people;
    }
}
