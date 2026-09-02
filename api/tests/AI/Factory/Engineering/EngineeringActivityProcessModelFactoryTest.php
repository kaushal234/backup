<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Engineering;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Engineering\EngineeringActivityProcessModelFactory;
use App\AI\Factory\Engineering\MasterEngineeringActivityProcessModelFactory;
use Doctrine\Common\Collections\ArrayCollection;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;
use LegacyBundle\Entity\Engineering\EngineeringActivityProcess;
use LegacyBundle\Entity\Engineering\EngineeringActivityProcessPart;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\EconomicsHeader;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\LifecycleDates;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\MasterEngineeringActivityProcess;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\NonRecurringCosts;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\PlannedCompletionDates;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\RecurringCosts;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\Scoring;
use PHPUnit\Framework\TestCase;

final class EngineeringActivityProcessModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(EngineeringActivityProcess::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsAllScalarFields(): void
    {
        $eap = $this->makeEap();
        $eap->shortDescription = 'short';
        $eap->description = 'long description';
        $eap->status = 'OPEN';
        $eap->openedAt = new \DateTimeImmutable('2025-01-01');
        $eap->closedAt = new \DateTimeImmutable('2025-02-01');
        $eap->category = 'BUG';
        $eap->importanceFactor = 'HIGH';
        $eap->type = 'TYPE';
        $eap->model = 'MODEL';
        $eap->actionPlan = 'plan';
        $eap->currency = 'EUR';
        $eap->additionalInformation = 'info';
        $eap->expectedHours = 12;

        $model = $this->makeFactory()->create($eap);

        self::assertSame('short', $model->shortDescription);
        self::assertSame('long description', $model->description);
        self::assertSame('OPEN', $model->status);
        self::assertSame('2025-01-01', $model->openedAt->format('Y-m-d'));
        self::assertSame('2025-02-01', $model->closedAt?->format('Y-m-d'));
        self::assertSame('BUG', $model->category);
        self::assertSame('HIGH', $model->importanceFactor);
        self::assertSame('TYPE', $model->type);
        self::assertSame('MODEL', $model->model);
        self::assertSame('plan', $model->actionPlan);
        self::assertSame('EUR', $model->currency);
        self::assertSame('info', $model->additionalInformation);
        self::assertSame(12, $model->expectedHours);
    }

    public function testCreateMapsRelations(): void
    {
        $master = new MasterEngineeringActivityProcess();
        $master->shortDescription = 'master short';
        $master->description = 'master description';
        $master->factory = new LocationById();
        $master->factory->name = '';
        $master->factory->erp = 0;
        $master->poster = $this->makePeople('', '');
        $master->projectLeader = $this->makePeople('', '');
        $master->economicsHeader = new EconomicsHeader();
        $master->nonRecurringCosts = new NonRecurringCosts();
        $master->recurringCosts = new RecurringCosts();
        $master->plannedCompletionDates = new PlannedCompletionDates();
        $master->lifecycleDates = new LifecycleDates();
        $master->scoring = new Scoring();

        $factory = new LocationById();
        $factory->name = 'PARIS';
        $factory->erp = 1;

        $reporter = $this->makePeople('Alice', 'Reporter');
        $poster = $this->makePeople('Bob', 'Poster');
        $assignee = $this->makePeople('Carol', 'Assignee');

        $part1 = new EngineeringActivityProcessPart();
        $part1->partNumber = 'PN-1';
        $part2 = new EngineeringActivityProcessPart();
        $part2->partNumber = 'PN-2';

        $eap = $this->makeEap();
        $eap->masterEngineeringActivityProcess = $master;
        $eap->factory = $factory;
        $eap->reportedBy = $reporter;
        $eap->poster = $poster;
        $eap->assignee = $assignee;
        $eap->parts = new ArrayCollection([$part1, $part2]);

        $model = $this->makeFactory()->create($eap);

        self::assertNotNull($model->master);
        self::assertSame('master short', $model->master->shortDescription);
        self::assertSame('master description', $model->master->description);

        self::assertNotNull($model->factory);
        self::assertSame('PARIS', $model->factory->name);

        self::assertNotNull($model->reportedBy);
        self::assertSame('Alice', $model->reportedBy->firstname);
        self::assertSame('Reporter', $model->reportedBy->lastname);

        self::assertNotNull($model->poster);
        self::assertSame('Bob', $model->poster->firstname);
        self::assertSame('Poster', $model->poster->lastname);

        self::assertNotNull($model->assignee);
        self::assertSame('Carol', $model->assignee->firstname);
        self::assertSame('Assignee', $model->assignee->lastname);

        self::assertCount(2, $model->parts);
        self::assertSame('PN-1', $model->parts[0]->partNumber);
        self::assertSame('PN-2', $model->parts[1]->partNumber);
    }

    public function testCreateLeavesNullRelationsAsNull(): void
    {
        $eap = $this->makeEap();
        $eap->masterEngineeringActivityProcess = null;
        $eap->factory = null;
        $eap->reportedBy = null;
        $eap->poster = null;
        $eap->assignee = null;

        $model = $this->makeFactory()->create($eap);

        self::assertNull($model->master);
        self::assertNull($model->factory);
        self::assertNull($model->reportedBy);
        self::assertNull($model->poster);
        self::assertNull($model->assignee);
        self::assertSame([], $model->parts);
    }

    private function makeFactory(): EngineeringActivityProcessModelFactory
    {
        $peopleFactory = new UserModelFactory();
        $locationFactory = new LocationModelFactory();

        return new EngineeringActivityProcessModelFactory(
            new MasterEngineeringActivityProcessModelFactory($peopleFactory, $locationFactory),
            $peopleFactory,
            $locationFactory,
        );
    }

    private function makeEap(): EngineeringActivityProcess
    {
        $eap = new EngineeringActivityProcess();
        $eap->shortDescription = '';
        $eap->description = '';
        $eap->status = '';
        $eap->openedAt = new \DateTimeImmutable();
        $eap->category = '';
        $eap->type = '';
        $eap->model = '';
        $eap->parts = new ArrayCollection();

        return $eap;
    }

    private function makePeople(string $firstname, string $lastname): PeopleById
    {
        $people = new PeopleById();
        $people->firstname = $firstname;
        $people->lastname = $lastname;
        $people->username = mb_strtolower($firstname);
        $people->email = mb_strtolower($firstname).'@x.test';

        return $people;
    }
}
