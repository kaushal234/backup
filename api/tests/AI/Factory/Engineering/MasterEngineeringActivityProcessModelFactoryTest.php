<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Engineering;

use App\AI\Factory\Engineering\MasterEngineeringActivityProcessModelFactory;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\EconomicsHeader;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\LifecycleDates;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\MasterEngineeringActivityProcess;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\NonRecurringCosts;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\PlannedCompletionDates;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\RecurringCosts;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\Scoring;
use PHPUnit\Framework\TestCase;

final class MasterEngineeringActivityProcessModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = new MasterEngineeringActivityProcessModelFactory(new \App\AI\Factory\Directory\UserModelFactory(), new \App\AI\Factory\Directory\LocationModelFactory());

        self::assertTrue($factory->supports(MasterEngineeringActivityProcess::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsAllFields(): void
    {
        $entity = $this->makeEntity();
        $entity->productType = 'TYPE_A';
        $entity->model = 'MODEL_X';
        $entity->status = 'OPEN';
        $entity->isPrivate = 'Y';
        $entity->type = 'TYPE';
        $entity->purpose = 'purpose';
        $entity->shortDescription = 'short';
        $entity->description = 'long description';
        $entity->resolution = 'resolution';
        $entity->rejectionReason = 'rejection';

        $model = (new MasterEngineeringActivityProcessModelFactory(new \App\AI\Factory\Directory\UserModelFactory(), new \App\AI\Factory\Directory\LocationModelFactory()))->create($entity);

        self::assertSame('TYPE_A', $model->productType);
        self::assertSame('MODEL_X', $model->model);
        self::assertSame('OPEN', $model->status);
        self::assertTrue($model->isPrivate);
        self::assertSame('TYPE', $model->type);
        self::assertSame('purpose', $model->purpose);
        self::assertSame('short', $model->shortDescription);
        self::assertSame('long description', $model->description);
        self::assertSame('resolution', $model->resolution);
        self::assertSame('rejection', $model->rejectionReason);
    }

    public function testCreateMapsRelationsAndEmbeddables(): void
    {
        $entity = $this->makeEntity();
        $entity->factory->name = 'PARIS';
        $entity->poster->firstname = 'Bob';
        $entity->poster->lastname = 'Poster';
        $entity->projectLeader->firstname = 'Carol';
        $entity->projectLeader->lastname = 'Leader';
        $entity->economicsHeader->isEngineeringProgramCapitalized = 'Y';
        $entity->economicsHeader->programCurrency = 'EUR';
        $entity->economicsHeader->costCalculationMethod = 'STD';
        $entity->scoring->importanceFactor = 5;
        $entity->scoring->finalWeight = 80;
        $entity->lifecycleDates->createdOn = new \DateTimeImmutable('2025-01-02');
        $entity->lifecycleDates->closedOn = new \DateTimeImmutable('2025-06-03');
        $entity->lifecycleDates->suspendedOn = new \DateTimeImmutable('2025-03-04');
        $entity->lifecycleDates->suspendedDaysCount = 7;
        $entity->plannedCompletionDates->milestone0 = new \DateTimeImmutable('2025-04-01');

        $model = (new MasterEngineeringActivityProcessModelFactory(new \App\AI\Factory\Directory\UserModelFactory(), new \App\AI\Factory\Directory\LocationModelFactory()))->create($entity);

        self::assertNotNull($model->factory);
        self::assertSame('PARIS', $model->factory->name);
        self::assertNotNull($model->poster);
        self::assertSame('Bob', $model->poster->firstname);
        self::assertNotNull($model->projectLeader);
        self::assertSame('Carol', $model->projectLeader->firstname);
        self::assertTrue($model->economicsHeader->isEngineeringProgramCapitalized);
        self::assertSame('EUR', $model->economicsHeader->programCurrency);
        self::assertSame('STD', $model->economicsHeader->costCalculationMethod);
        self::assertSame(5, $model->scoring->importanceFactor);
        self::assertSame(80, $model->scoring->finalWeight);
        self::assertSame('2025-01-02', $model->lifecycleDates->createdOn?->format('Y-m-d'));
        self::assertSame('2025-06-03', $model->lifecycleDates->closedOn?->format('Y-m-d'));
        self::assertSame('2025-03-04', $model->lifecycleDates->suspendedOn?->format('Y-m-d'));
        self::assertSame(7, $model->lifecycleDates->suspendedDaysCount);
        self::assertSame('2025-04-01', $model->plannedCompletionDates->milestone0?->format('Y-m-d'));
    }

    private function makeEntity(): MasterEngineeringActivityProcess
    {
        $entity = new MasterEngineeringActivityProcess();
        $entity->factory = new LocationById();
        $entity->factory->name = '';
        $entity->factory->erp = 0;
        $entity->poster = new PeopleById();
        $entity->poster->firstname = '';
        $entity->poster->lastname = '';
        $entity->poster->username = '';
        $entity->poster->email = '';
        $entity->projectLeader = new PeopleById();
        $entity->projectLeader->firstname = '';
        $entity->projectLeader->lastname = '';
        $entity->projectLeader->username = '';
        $entity->projectLeader->email = '';
        $entity->economicsHeader = new EconomicsHeader();
        $entity->nonRecurringCosts = new NonRecurringCosts();
        $entity->recurringCosts = new RecurringCosts();
        $entity->plannedCompletionDates = new PlannedCompletionDates();
        $entity->lifecycleDates = new LifecycleDates();
        $entity->scoring = new Scoring();

        return $entity;
    }
}
