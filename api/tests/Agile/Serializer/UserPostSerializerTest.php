<?php

declare(strict_types=1);

namespace App\Tests\Agile\Serializer;

use App\Agile\Resources\User;
use App\Agile\Serializer\UserPostSerializer;
use PHPUnit\Framework\TestCase;

class UserPostSerializerTest extends TestCase
{
    private UserPostSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new UserPostSerializer();
    }

    public function testTransformNewUser(): void
    {
        $user = new User();
        $user->isNew = true;
        $user->peopleId = '123';
        $user->email = 'test@example.com';
        $user->firstName = 'John';
        $user->lastName = 'Doe';
        $user->jobTitle = 'Engineer';
        $user->managerPeopleId = '456';
        $user->managerAgileId = 'lalala';
        $user->positionCategory = 'maPositionCategory';
        $user->languageCode = 'EN';
        $user->position = 'Senior Engineer';
        $user->department = 'Engineering';
        $user->address = '123 Main St';
        $user->contracttype = 'Permanent';
        $user->street1 = 'Main St';
        $user->street2 = '';
        $user->city = 'Somewhere';
        $user->country = 'USA';
        $user->zipcode = '12345';
        $user->region = 'Midwest';
        $user->businessunit = 'Tech';
        $user->subdivision = 'Development';
        $user->division = 'Software';
        $user->loginMethod = 'Password';
        $user->timeZone = 'America/New_York';
        $user->setStartDate('2024-12-24');

        $expected = [
            'startDate' => '2024-12-24T00:00:00-05:00',
            'timeZone' => 'America/New_York',
            'ref' => '123',
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'jobTitle' => 'Engineer',
            'managerRef' => '456',
            'languageCode' => 'EN',
            'position' => 'Senior Engineer',
            'positioncategory' => 'maPositionCategory',
            'department' => 'Engineering',
            'address' => '123 Main St',
            'contracttype' => 'Permanent',
            'street1' => 'Main St',
            'street2' => '',
            'city' => 'Somewhere',
            'country' => 'USA',
            'zipcode' => '12345',
            'region' => 'Midwest',
            'businessunit' => 'Tech',
            'subdivision' => 'Development',
            'division' => 'Software',
            'loginMethod' => 'Password',
        ];

        $result = $this->serializer->serialize($user);
        $this->assertSame($expected, $result);
    }

    public function testTransformExistingUser(): void
    {
        $user = new User();
        $user->isNew = false;
        $user->peopleId = '123';
        $user->email = 'test@example.com';

        $expected = [
            'ref' => '123',
            'email' => 'test@example.com',
            'firstName' => '',
            'lastName' => '',
            'jobTitle' => '',
            'managerRef' => '',
            'languageCode' => 'fr',
            'position' => '',
            'positioncategory' => '',
            'department' => '',
            'address' => '',
            'contracttype' => '',
            'street1' => '',
            'street2' => '',
            'city' => '',
            'country' => '',
            'zipcode' => '',
            'region' => '',
            'businessunit' => '',
            'subdivision' => '',
            'division' => '',
            'loginMethod' => 'email',
        ];

        $result = $this->serializer->serialize($user);
        $this->assertSame($expected, $result);
    }

    public function testTransformThrowsExceptionIfPeopleIdIsEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The peopleId cannot be empty.');

        $user = new User();
        $user->peopleId = '';
        $this->serializer->serialize($user);
    }

    public function testTransformUserWithNullProperties(): void
    {
        $user = new User();
        $user->isNew = true;
        $user->peopleId = '123';
        $user->email = 'test@example.com';

        $expected = [
            'startDate' => '',
            'timeZone' => 'America/New_York',
            'ref' => '123',
            'email' => 'test@example.com',
            'firstName' => '',
            'lastName' => '',
            'jobTitle' => '',
            'managerRef' => '',
            'languageCode' => 'fr',
            'position' => '',
            'positioncategory' => '',
            'department' => '',
            'address' => '',
            'contracttype' => '',
            'street1' => '',
            'street2' => '',
            'city' => '',
            'country' => '',
            'zipcode' => '',
            'region' => '',
            'businessunit' => '',
            'subdivision' => '',
            'division' => '',
            'loginMethod' => 'email',
        ];

        $result = $this->serializer->serialize($user);
        $this->assertSame($expected, $result);
    }
}
