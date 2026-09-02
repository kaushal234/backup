<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\MIS;

use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\MIS\TroubleTicketModelFactory;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\MIS\TroubleTicket\Type;
use App\Entity\Module\Module;
use PHPUnit\Framework\TestCase;

final class TroubleTicketModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(TroubleTicket::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = $this->makeEntity();
        $entity->shortDescription = 'tt short';
        $entity->description = 'tt desc';

        $model = $this->makeFactory()->create($entity);

        self::assertSame('tt short', $model->shortDescription);
        self::assertSame('tt desc', $model->description);
        self::assertSame('BUG', $model->type->type);
        self::assertNull($model->module);
        self::assertNull($model->createdBy);
        self::assertNull($model->assignee);
        self::assertNull($model->misAssignee);
        self::assertSame([], $model->ccs);
        self::assertSame([], $model->additionalOwners);
    }

    public function testCreateAllowsNullableScalars(): void
    {
        $entity = $this->makeEntity();
        $entity->url = null;
        $entity->referer = null;
        $entity->hostName = null;
        $entity->jiraIssueNumber = null;
        $entity->satisfaction = null;
        $entity->indiceFactor = null;
        $entity->dueDate = null;
        $entity->closedAt = null;
        $entity->solutionProposedAt = null;
        $entity->lastCommentedAt = null;

        $model = $this->makeFactory()->create($entity);

        self::assertNull($model->url);
        self::assertNull($model->referer);
        self::assertNull($model->hostName);
        self::assertNull($model->jiraIssueNumber);
        self::assertNull($model->satisfaction);
        self::assertNull($model->indiceFactor);
        self::assertNull($model->dueDate);
        self::assertNull($model->closedAt);
        self::assertNull($model->solutionProposedAt);
        self::assertNull($model->lastCommentedAt);
    }

    public function testCreateMapsRelations(): void
    {
        $module = $this->createMock(Module::class);
        $module->method('getName')->willReturn('mis');

        $people = $this->createPeopleMock('alice');
        $cc = $this->createPeopleMock('cc1');
        $owner = $this->createPeopleMock('owner1');

        $entity = $this->makeEntity();
        $entity->module = $module;
        $entity->createdBy = $people;
        $entity->assignee = $people;
        $entity->misAssignee = $people;
        $entity->addCc($cc);
        $entity->addAdditionalOwner($owner);

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->module);
        self::assertSame('mis', $model->module->name);
        self::assertNotNull($model->createdBy);
        self::assertSame('alice', $model->createdBy->username);
        self::assertCount(1, $model->ccs);
        self::assertSame('cc1', $model->ccs[0]->username);
        self::assertCount(1, $model->additionalOwners);
        self::assertSame('owner1', $model->additionalOwners[0]->username);
    }

    private function makeFactory(): TroubleTicketModelFactory
    {
        return new TroubleTicketModelFactory(new UserModelFactory());
    }

    private function makeEntity(): TroubleTicket
    {
        $type = new Type();
        $type->type = 'BUG';
        $type->description = 'a bug';

        $entity = new TroubleTicket();
        $entity->createdAt = new \DateTime('2025-01-01');
        $entity->shortDescription = '';
        $entity->description = '';
        $entity->type = $type;
        $entity->url = '';
        $entity->referer = '';
        $entity->hostName = '';

        return $entity;
    }

    private function createPeopleMock(string $username): People
    {
        $people = $this->createMock(People::class);
        $people->method('getUsername')->willReturn($username);
        $people->method('getEmail')->willReturn($username.'@x.test');
        $people->method('getFirstname')->willReturn(ucfirst($username));
        $people->method('getLastname')->willReturn('X');

        return $people;
    }
}
