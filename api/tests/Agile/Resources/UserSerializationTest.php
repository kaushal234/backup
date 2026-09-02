<?php

declare(strict_types=1);

namespace App\Tests\Agile\Resources;

use App\Agile\Resources\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

class UserSerializationTest extends KernelTestCase
{
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->serializer = $this->getContainer()->get(SerializerInterface::class);
    }

    public function testDeserialization(): void
    {
        $data = [
            'positions' => [
                [
                    'manager' => ['id' => '1234'],
                    'title' => 'Software Engineer',
                    'startDate' => '2024-01-01',
                ],
            ],
            'ref' => '5678',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'timeZone' => 'Europe/London',
            'additionalFields' => [
                'position' => 'Developer',
                'department' => 'Engineering',
                'region' => 'EMEA',
                'businessunit' => 'BU1',
                'subdivision' => 'Subdivision1',
                'division' => 'Division1',
                'address' => '123 Main St',
                'contracttype' => 'Permanent',
                'street1' => 'Main St',
                'street2' => 'Apt 4B',
                'city' => 'London',
                'country' => 'UK',
                'zipcode' => 'WC1X 0AA',
            ],
            'email' => 'john.doe@example.com',
            'status' => 'active',
        ];

        /** @var User $user */
        $user = $this->serializer->deserialize(json_encode($data), User::class, 'json');

        $this->assertSame('1234', $user->managerAgileId);
        $this->assertSame('5678', $user->peopleId);
        $this->assertSame('Software Engineer', $user->jobTitle);
        $this->assertSame('John', $user->firstName);
        $this->assertSame('Doe', $user->lastName);
        $this->assertSame('Europe/London', $user->timeZone);
        $this->assertSame('fr', $user->languageCode);
        $this->assertSame('Developer', $user->position);
        $this->assertSame('Engineering', $user->department);
        $this->assertSame('EMEA', $user->region);
        $this->assertSame('BU1', $user->businessunit);
        $this->assertSame('Subdivision1', $user->subdivision);
        $this->assertSame('Division1', $user->division);
        $this->assertSame('123 Main St', $user->address);
        $this->assertSame('Permanent', $user->contracttype);
        $this->assertSame('Main St', $user->street1);
        $this->assertSame('Apt 4B', $user->street2);
        $this->assertSame('London', $user->city);
        $this->assertSame('UK', $user->country);
        $this->assertSame('WC1X 0AA', $user->zipcode);
        $this->assertSame('john.doe@example.com', $user->email);
        $this->assertSame('2024-01-01', $user->getStartDate());
        $this->assertTrue($user->isActive());

        $data['status'] = 'new';
        $user2 = $this->serializer->deserialize(json_encode($data), User::class, 'json');
        $this->assertTrue($user2->isActive());

        $data['status'] = 'inactive';
        $user3 = $this->serializer->deserialize(json_encode($data), User::class, 'json');
        $this->assertTrue(!$user3->isActive());
    }
}
