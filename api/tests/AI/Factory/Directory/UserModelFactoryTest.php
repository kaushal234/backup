<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Directory;

use App\AI\Factory\Directory\UserModelFactory;
use App\Entity\Directory\People;
use LegacyBundle\Entity\Directory\PeopleById as LegacyPeople;
use PHPUnit\Framework\TestCase;

final class UserModelFactoryTest extends TestCase
{
    public function testCreateFromLegacyPeople(): void
    {
        $entity = new LegacyPeople();
        $entity->username = 'alice';
        $entity->email = 'alice@x.test';
        $entity->firstname = 'Alice';
        $entity->lastname = 'X';

        $model = (new UserModelFactory())->create($entity);

        self::assertSame('alice', $model->username);
        self::assertSame('alice@x.test', $model->email);
        self::assertSame('Alice', $model->firstname);
        self::assertSame('X', $model->lastname);
    }

    public function testCreateFromAppPeople(): void
    {
        $entity = $this->createMock(People::class);
        $entity->method('getUsername')->willReturn('bob');
        $entity->method('getEmail')->willReturn('bob@x.test');
        $entity->method('getFirstname')->willReturn('Bob');
        $entity->method('getLastname')->willReturn('Y');

        $model = (new UserModelFactory())->create($entity);

        self::assertSame('bob', $model->username);
        self::assertSame('bob@x.test', $model->email);
        self::assertSame('Bob', $model->firstname);
        self::assertSame('Y', $model->lastname);
    }
}
