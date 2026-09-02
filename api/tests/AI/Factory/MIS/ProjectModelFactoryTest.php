<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\MIS;

use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\MIS\ProjectModelFactory;
use App\Entity\Directory\People;
use App\Entity\Directory\Region;
use App\Entity\MIS\Project\Phase;
use App\Entity\MIS\Project\Project;
use App\Entity\MIS\Project\ProjectTag;
use App\Entity\Module\Module;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

final class ProjectModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(Project::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $project = $this->getMockBuilder(Project::class)
            ->onlyMethods([
                'getStatus',
                'getModuleKeyUsers',
                'getMisMembers',
                'getPhases',
                'getTags',
            ])
            ->getMock();

        $project->name = 'Migration ERP';
        $project->description = 'ERP migration project';
        $project->indicesFactor = 'IF 10';
        $project->confidential = false;
        $project->createdAt = new \DateTimeImmutable('2025-01-01');
        $project->startedAt = new \DateTimeImmutable('2025-01-02');
        $project->lastCommentedAt = null;
        $project->lastComment = null;
        $project->conclusion = null;
        $project->teamsLink = null;

        $project->module = null;
        $project->region = null;
        $project->projectManager = null;
        $project->misOwner = null;

        $project->method('getStatus')->willReturn('OPEN');

        $project->method('getModuleKeyUsers')
            ->willReturn(new ArrayCollection());

        $project->method('getMisMembers')
            ->willReturn(new ArrayCollection());

        $project->method('getPhases')
            ->willReturn(new ArrayCollection());

        $project->method('getTags')
            ->willReturn(new ArrayCollection());

        $model = $this->makeFactory()->create($project);

        self::assertSame('Migration ERP', $model->name);
        self::assertSame('ERP migration project', $model->description);
        self::assertSame('IF 10', $model->indicesFactor);
        self::assertSame('OPEN', $model->status);
        self::assertFalse($model->confidential);

        self::assertNull($model->region);
        self::assertNull($model->module);
        self::assertNull($model->projectManager);
        self::assertNull($model->misOwner);

        self::assertSame([], $model->moduleKeyUsers);
        self::assertSame([], $model->misMembers);
        self::assertSame([], $model->phases);
        self::assertSame([], $model->tags);
    }

    public function testCreateMapsRelationsAndCollections(): void
    {
        $region = $this->createMock(Region::class);
        $region->method('getName')->willReturn('EMEA');

        $module = $this->createMock(Module::class);
        $module->method('getName')->willReturn('SAP');

        $projectManager = $this->createMock(People::class);
        $projectManager->method('getUsername')->willReturn('manager');

        $misOwner = $this->createMock(People::class);
        $misOwner->method('getUsername')->willReturn('owner');

        $moduleKeyUser = $this->createMock(People::class);
        $moduleKeyUser->method('getUsername')->willReturn('key-user');

        $misMember = $this->createMock(People::class);
        $misMember->method('getUsername')->willReturn('member');

        $phase = new Phase();
        $phase->number = 1;
        $phase->estimatedClosureAt = new \DateTimeImmutable('2025-06-01');
        $phase->revisedClosureAt = new \DateTimeImmutable('2025-06-15');
        $phase->estimatedHours = 100;
        $phase->revisedEstimatedHours = 120;

        $tag = $this->createMock(ProjectTag::class);
        $tag->method('getName')->willReturn('Critical');

        $project = $this->getMockBuilder(Project::class)
            ->onlyMethods([
                'getStatus',
                'getModuleKeyUsers',
                'getMisMembers',
                'getPhases',
                'getTags',
            ])
            ->getMock();

        $project->name = 'Migration ERP';
        $project->description = 'ERP migration project';
        $project->indicesFactor = 'IF 20';
        $project->confidential = true;
        $project->createdAt = new \DateTimeImmutable('2025-01-01');
        $project->startedAt = new \DateTimeImmutable('2025-02-01');
        $project->lastCommentedAt = new \DateTimeImmutable('2025-03-01');
        $project->lastComment = 'Last update';
        $project->conclusion = 'Successful';
        $project->teamsLink = 'https://teams.test';

        $project->region = $region;
        $project->module = $module;
        $project->projectManager = $projectManager;
        $project->misOwner = $misOwner;

        $project->method('getStatus')->willReturn('IN_PROGRESS');

        $project->method('getModuleKeyUsers')
            ->willReturn(new ArrayCollection([$moduleKeyUser]));

        $project->method('getMisMembers')
            ->willReturn(new ArrayCollection([$misMember]));

        $project->method('getPhases')
            ->willReturn(new ArrayCollection([$phase]));

        $project->method('getTags')
            ->willReturn(new ArrayCollection([$tag]));

        $model = $this->makeFactory()->create($project);

        self::assertSame('EMEA', $model->region);

        self::assertNotNull($model->module);
        self::assertSame('SAP', $model->module->name);

        self::assertNotNull($model->projectManager);
        self::assertSame('manager', $model->projectManager->username);

        self::assertNotNull($model->misOwner);
        self::assertSame('owner', $model->misOwner->username);

        self::assertCount(1, $model->moduleKeyUsers);
        self::assertSame('key-user', $model->moduleKeyUsers[0]->username);

        self::assertCount(1, $model->misMembers);
        self::assertSame('member', $model->misMembers[0]->username);

        self::assertCount(1, $model->phases);
        self::assertSame(1, $model->phases[0]->number);
        self::assertSame(100, $model->phases[0]->estimatedHours);
        self::assertSame(120, $model->phases[0]->revisedEstimatedHours);

        self::assertCount(1, $model->tags);
        self::assertSame('Critical', $model->tags[0]);
    }

    private function makeFactory(): ProjectModelFactory
    {
        return new ProjectModelFactory(
            new UserModelFactory(),
        );
    }
}
