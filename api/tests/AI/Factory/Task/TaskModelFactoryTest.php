<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Task;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Task\TaskModelFactory;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Module\Module;
use App\Entity\Task\Task;
use PHPUnit\Framework\TestCase;

final class TaskModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(Task::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = $this->makeEntity();
        $entity->shortDescription = 'task short';
        $entity->description = 'task desc';

        $model = $this->makeFactory()->create($entity);

        self::assertSame(Task::PENDING, $model->status);
        self::assertSame('task short', $model->shortDescription);
        self::assertSame('task desc', $model->description);
        self::assertSame('LYON', $model->location->name);
        self::assertNull($model->module);
        self::assertNull($model->createdBy);
        self::assertNull($model->assignee);
        self::assertSame([], $model->recipients);
    }

    public function testCreateAllowsNullableOptionalFields(): void
    {
        $entity = $this->makeEntity();
        $entity->dueDate = null;
        $entity->closedAt = null;
        $entity->startedAt = null;
        $entity->lastComment = null;
        $entity->closeComment = null;
        $entity->indiceFactor = null;

        $model = $this->makeFactory()->create($entity);

        self::assertNull($model->dueDate);
        self::assertNull($model->closedAt);
        self::assertNull($model->startedAt);
        self::assertNull($model->lastComment);
        self::assertNull($model->closeComment);
        self::assertNull($model->indiceFactor);
    }

    public function testCreateMapsModuleAndRecipients(): void
    {
        $module = $this->createMock(Module::class);
        $module->method('getName')->willReturn('quality');

        $recipient = $this->createMock(People::class);
        $recipient->method('getUsername')->willReturn('alice');
        $recipient->method('getEmail')->willReturn('alice@x.test');
        $recipient->method('getFirstname')->willReturn('Alice');
        $recipient->method('getLastname')->willReturn('X');

        $entity = $this->makeEntity();
        $entity->module = $module;
        $entity->addRecipient($recipient);

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->module);
        self::assertSame('quality', $model->module->name);
        self::assertCount(1, $model->recipients);
        self::assertSame('alice', $model->recipients[0]->username);
    }

    private function makeFactory(): TaskModelFactory
    {
        return new TaskModelFactory(new UserModelFactory(), new LocationModelFactory());
    }

    private function makeEntity(): Task
    {
        $location = $this->createMock(Location::class);
        $location->method('getName')->willReturn('LYON');
        $location->method('getErp')->willReturn(1);

        $entity = new Task();
        $entity->createdAt = new \DateTime('2025-01-01');
        $entity->referenceId = 0;
        $entity->shortDescription = '';
        $entity->description = '';
        $entity->location = $location;
        $entity->module = null;
        $entity->createdBy = null;
        $entity->assignee = null;
        $entity->startedAt = null;
        $entity->escalationDate = null;

        return $entity;
    }
}
