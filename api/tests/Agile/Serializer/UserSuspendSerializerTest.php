<?php

declare(strict_types=1);

namespace App\Tests\Agile\Serializer;

use App\Agile\Resources\User;
use App\Agile\Serializer\UserSuspendSerializer;
use PHPUnit\Framework\TestCase;

class UserSuspendSerializerTest extends TestCase
{
    private UserSuspendSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new UserSuspendSerializer();
    }

    public function testTransformThrowsExceptionIfPeopleIdIsEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The peopleId cannot be empty.');

        $user = new User();
        $user->peopleId = '';  // Simulating an empty peopleId
        $this->serializer->serialize($user);
    }

    public function testTransformReturnsArrayWithRefWhenValidUser(): void
    {
        $user = new User();
        $user->peopleId = '12345';

        $result = $this->serializer->serialize($user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('ref', $result);
        $this->assertSame('12345', $result['ref']);
    }
}
