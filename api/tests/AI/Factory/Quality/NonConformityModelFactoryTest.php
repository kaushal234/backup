<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Quality;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Quality\NonConformityModelFactory;
use App\AI\Factory\Support\EquipmentRecordModelFactory;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Finance\Currency;
use App\Entity\Quality\NonConformity;
use App\Entity\Quality\Process;
use App\Entity\Quality\Responsible;
use PHPUnit\Framework\TestCase;

final class NonConformityModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = new NonConformityModelFactory(new UserModelFactory(), new LocationModelFactory(), new EquipmentRecordModelFactory(new LocationModelFactory()));

        self::assertTrue($factory->supports(NonConformity::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsAndRelations(): void
    {
        $location = $this->createMock(Location::class);
        $location->method('getName')->willReturn('PARIS');
        $location->method('getErp')->willReturn(42);

        $reporter = $this->createMock(People::class);
        $reporter->method('getUsername')->willReturn('alice');
        $reporter->method('getEmail')->willReturn('alice@tld.com');
        $reporter->method('getFirstname')->willReturn('Alice');
        $reporter->method('getLastname')->willReturn('REPORTER');

        $currency = $this->createMock(Currency::class);
        $currency->method('getName')->willReturn('EUR');

        $process = new Process();
        $process->category = Process::ENGINEERING;
        $process->description = 'design';

        $responsible = new Responsible();
        $responsible->name = Responsible::TLD;

        $entity = new NonConformity();
        $entity->location = $location;
        $entity->createdAt = new \DateTime('2025-03-01');
        $entity->status = NonConformity::IN_PROGRESS;
        $entity->hours = 5;
        $entity->problem = 'broken bolt';
        $entity->shortDescription = 'bolt';
        $entity->solution = 'replace';
        $entity->purchaseOrderNumber = 'PO-1';
        $entity->rush = true;
        $entity->chargeVendor = false;
        $entity->failureType = NonConformity::MECHANICAL;
        $entity->iFactor = 'A';
        $entity->investigation = '5why';
        $entity->scrap = false;
        $entity->rework = true;
        $entity->firstArticleInspection = false;
        $entity->useAsIs = false;
        $entity->derogation = false;
        $entity->returnVendor = false;
        $entity->chargeVendorForRepair = false;
        $entity->supplierCorrectiveActionRequest = false;
        $entity->internalCorrectiveActionRequest = false;
        $entity->other = false;
        $entity->containment = true;
        $entity->actionComment = 'noted';
        $entity->repairApprovalDate = new \DateTime('2025-03-05');
        $entity->costBreakdown = 'breakdown';
        $entity->cost = 99.5;
        $entity->workOrderReference = 'WO-1';
        $entity->nonQualityCost = 12.0;
        $entity->invoiceNumber = 'INV-1';
        $entity->environmentalIssue = false;
        $entity->safety = true;
        $entity->reportedBy = $reporter;
        $entity->repairApprover = null;
        $entity->currency = $currency;
        $entity->setSupplierName('Acme');
        $entity->setSupplierNumber('SUP-9');
        $entity->addProcess($process);
        $entity->addResponsible($responsible);

        $model = (new NonConformityModelFactory(new UserModelFactory(), new LocationModelFactory(), new EquipmentRecordModelFactory(new LocationModelFactory())))->create($entity);

        self::assertSame(NonConformity::IN_PROGRESS, $model->status);
        self::assertSame('2025-03-01', $model->createdAt->format('Y-m-d'));
        self::assertSame(5, $model->hours);
        self::assertSame('broken bolt', $model->problem);
        self::assertSame('bolt', $model->shortDescription);
        self::assertSame('replace', $model->solution);
        self::assertSame('PO-1', $model->purchaseOrderNumber);
        self::assertTrue($model->rush);
        self::assertSame(NonConformity::MECHANICAL, $model->failureType);
        self::assertSame('A', $model->iFactor);
        self::assertSame('5why', $model->investigation);
        self::assertTrue($model->rework);
        self::assertTrue($model->containment);
        self::assertSame('noted', $model->actionComment);
        self::assertSame('2025-03-05', $model->repairApprovalDate?->format('Y-m-d'));
        self::assertSame('breakdown', $model->costBreakdown);
        self::assertSame(99.5, $model->cost);
        self::assertSame('WO-1', $model->workOrderReference);
        self::assertSame(12.0, $model->nonQualityCost);
        self::assertSame('INV-1', $model->invoiceNumber);
        self::assertTrue($model->safety);
        self::assertSame('Acme', $model->supplierName);
        self::assertSame('SUP-9', $model->supplierNumber);

        self::assertSame('PARIS', $model->location->name);
        self::assertSame(42, $model->location->erp);

        self::assertNotNull($model->reportedBy);
        self::assertSame('alice', $model->reportedBy->username);
        self::assertSame('alice@tld.com', $model->reportedBy->email);
        self::assertSame('Alice', $model->reportedBy->firstname);
        self::assertSame('REPORTER', $model->reportedBy->lastname);
        self::assertNull($model->repairApprover);

        self::assertNotNull($model->currency);
        self::assertSame('EUR', $model->currency->name);

        self::assertCount(1, $model->processes);
        self::assertSame('design', $model->processes[0]->description);

        self::assertCount(1, $model->responsibles);
        self::assertSame(Responsible::TLD, $model->responsibles[0]->name);

        self::assertSame([], $model->crabs);
        self::assertSame([], $model->parts);
        self::assertSame([], $model->products);
        self::assertSame([], $model->equipmentRecords);
    }
}
