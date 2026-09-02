<?php

declare(strict_types=1);

namespace App\Tests\AI\Tool\Directory;

use App\AI\Tool\Directory\SearchPeopleTool;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Department;
use App\Entity\Directory\People;
use App\Entity\Directory\Phone;
use App\Entity\Directory\Position;
use App\Repository\Directory\PeopleRepository;
use PHPUnit\Framework\TestCase;

final class SearchPeopleToolTest extends TestCase
{
    public function testReturnsNotFoundWhenQueryIsEmpty(): void
    {
        $repository = $this->createMock(PeopleRepository::class);
        $repository->expects(self::never())->method('searchActiveByName');

        $tool = new SearchPeopleTool($repository);

        self::assertSame(['status' => 'not_found', 'query' => ''], ($tool)('   '));
    }

    public function testReturnsNotFoundWhenRepositoryReturnsEmpty(): void
    {
        $repository = $this->createMock(PeopleRepository::class);
        $repository->method('searchActiveByName')->with('Doe', 10)->willReturn([]);

        $tool = new SearchPeopleTool($repository);

        self::assertSame(['status' => 'not_found', 'query' => 'Doe'], ($tool)('Doe'));
    }

    public function testReturnsFullPersonWhenExactlyOneMatch(): void
    {
        $supervisor = $this->makePerson('Alice', 'MANAGER', 'alice.manager@alvest.com');

        $bu = new BusinessUnit();
        $bu->setName('GSE France');

        $position = new Position();
        $position->setDescription('Senior Engineer');

        $department = new Department();
        $department->setName('R&D');

        $direct = new Phone();
        $direct->setType(Phone::TYPE_PHONE);
        $direct->setNumber('+33 1 23 45 67 89');

        $mobile = new Phone();
        $mobile->setType(Phone::TYPE_MOBILE);
        $mobile->setNumber('+33 6 12 34 56 78');

        $person = $this->makePerson('Jean', 'DUPONT', 'jean.dupont@alvest.com');
        $person->setJobTitle('Lead engineer');
        $person->setBusinessUnit($bu);
        $person->setPosition($position);
        $person->setDepartment($department);
        $person->setSupervisor($supervisor);
        $person->addPhone($direct);
        $person->addPhone($mobile);

        $repository = $this->createMock(PeopleRepository::class);
        $repository->method('searchActiveByName')->with('Jean Dupont', 10)->willReturn([$person]);

        $tool = new SearchPeopleTool($repository);

        $result = ($tool)('Jean Dupont');

        self::assertSame('found', $result['status']);
        self::assertSame([
            'firstname' => 'Jean',
            'lastname' => 'DUPONT',
            'email' => 'jean.dupont@alvest.com',
            'jobTitle' => 'Lead engineer',
            'businessUnit' => 'GSE France',
            'position' => 'Senior Engineer',
            'department' => 'R&D',
            'supervisor' => 'Alice MANAGER',
            'phones' => [
                Phone::TYPE_PHONE => '+33 1 23 45 67 89',
                Phone::TYPE_MOBILE => '+33 6 12 34 56 78',
            ],
        ], $result['person']);
    }

    public function testReturnsCandidatesWhenMultipleMatches(): void
    {
        $bu = new BusinessUnit();
        $bu->setName('GSE France');

        $department = new Department();
        $department->setName('Sales');

        $p1 = $this->makePerson('Jean', 'DUPONT', 'jean.dupont@alvest.com');
        $p1->setJobTitle('Sales Manager');
        $p1->setBusinessUnit($bu);
        $p1->setDepartment($department);

        $p2 = $this->makePerson('Jean', 'DURAND', 'jean.durand@alvest.com');
        $p2->setJobTitle('Engineer');

        $p3 = $this->makePerson('Jeanne', 'MARTIN', 'jeanne.martin@alvest.com');

        $repository = $this->createMock(PeopleRepository::class);
        $repository->method('searchActiveByName')->with('Jean', 10)->willReturn([$p1, $p2, $p3]);

        $tool = new SearchPeopleTool($repository);

        $result = ($tool)('Jean');

        self::assertSame('multiple', $result['status']);
        self::assertSame('Jean', $result['query']);
        self::assertSame(3, $result['count']);
        self::assertSame([
            [
                'firstname' => 'Jean',
                'lastname' => 'DUPONT',
                'jobTitle' => 'Sales Manager',
                'businessUnit' => 'GSE France',
                'department' => 'Sales',
            ],
            [
                'firstname' => 'Jean',
                'lastname' => 'DURAND',
                'jobTitle' => 'Engineer',
                'businessUnit' => null,
                'department' => null,
            ],
            [
                'firstname' => 'Jeanne',
                'lastname' => 'MARTIN',
                'jobTitle' => null,
                'businessUnit' => null,
                'department' => null,
            ],
        ], $result['candidates']);
    }

    public function testHandlesNullableFields(): void
    {
        $person = $this->makePerson('Solo', 'PERSON', 'solo@alvest.com');

        $repository = $this->createMock(PeopleRepository::class);
        $repository->method('searchActiveByName')->willReturn([$person]);

        $tool = new SearchPeopleTool($repository);

        $result = ($tool)('Solo');

        self::assertSame('found', $result['status']);
        self::assertSame([
            'firstname' => 'Solo',
            'lastname' => 'PERSON',
            'email' => 'solo@alvest.com',
            'jobTitle' => null,
            'businessUnit' => null,
            'position' => null,
            'department' => null,
            'supervisor' => null,
            'phones' => [],
        ], $result['person']);
    }

    private function makePerson(string $firstname, string $lastname, string $email): People
    {
        $person = new People();
        $person->setFirstname($firstname);
        $person->setLastname($lastname);
        $person->setEmail($email);

        return $person;
    }
}
