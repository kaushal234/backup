<?php

declare(strict_types=1);

namespace App\Tests\Agile\Factory;

use App\Agile\Factory\UserFactory;
use App\Agile\Resources\User;
use App\Agile\SynchronizationFilters;
use App\Entity\Directory\People;
use App\Entity\Directory\PositionCategory;
use App\Entity\Directory\PositionClassification;
use App\Repository\Directory\PositionClassificationRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class UserFactoryTest extends TestCase
{
    use ProphecyTrait;

    private UserFactory $userFactory;
    private ObjectProphecy $userRepository;
    private MockObject $positionClassificationRepository;

    protected function setUp(): void
    {
        $this->positionClassificationRepository = $this->createMock(PositionClassificationRepository::class);
        $this->userRepository = $this->prophesize(SynchronizationFilters::class);

        $this->userFactory = new UserFactory(
            $this->positionClassificationRepository,
        );
    }

    /**
     * @dataProvider peopleProvider
     */
    public function testCreateFromPeople(People $people, User $userExpected, array $agileUsers = []): void
    {
        $isNew = true;

        if ('' !== $userExpected->positionCategory) {
            $positionCategory = new PositionCategory();
            $positionCategory->name = $userExpected->positionCategory;
            $positionClassification = new PositionClassification();
            $positionClassification->positionCategory = $positionCategory;
            $this->positionClassificationRepository->expects($this->once())->method('getPositionClassificationForPeople')->with($people)->willReturn($positionClassification);
        } else {
            $this->positionClassificationRepository->expects($this->once())->method('getPositionClassificationForPeople')->with($people)->willReturn(null);
        }

        $user = $this->userFactory->createFromPeople($people, $isNew, $agileUsers);

        $this->assertSame($userExpected->peopleId, $user->peopleId);
        $this->assertSame($userExpected->email, $user->email);
        $this->assertSame($userExpected->jobTitle, $user->jobTitle);
        $this->assertSame($userExpected->firstName, $user->firstName);
        $this->assertSame($userExpected->lastName, $user->lastName);
        $this->assertSame($userExpected->managerPeopleId, $user->managerPeopleId);
        $this->assertSame($userExpected->managerAgileId, $user->managerAgileId);
        $this->assertSame($userExpected->isActive(), $user->isActive());
        $this->assertSame($userExpected->getStartDate(), $user->getStartDate());
        $this->assertSame($userExpected->timeZone, $user->timeZone);
        $this->assertSame($userExpected->languageCode, $user->languageCode);
        $this->assertSame($userExpected->position, $user->position);
        $this->assertSame($userExpected->department, $user->department);
        $this->assertSame($userExpected->businessunit, $user->businessunit);
        $this->assertSame($userExpected->region, $user->region);
        $this->assertSame($userExpected->subdivision, $user->subdivision);
        $this->assertSame($userExpected->division, $user->division);
        $this->assertSame($userExpected->address, $user->address);
        $this->assertSame($userExpected->contracttype, $user->contracttype);
        $this->assertSame($userExpected->street1, $user->street1);
        $this->assertSame($userExpected->street2, $user->street2);
        $this->assertSame($userExpected->city, $user->city);
        $this->assertSame($userExpected->country, $user->country);
        $this->assertSame($userExpected->zipcode, $user->zipcode);
        $this->assertSame($userExpected->positionCategory, $user->positionCategory);
        $this->assertSame(User::LOGIN_METHOD, $user->loginMethod);
        $this->assertSame($isNew, $user->isNew);
    }

    public function peopleProvider(): array
    {
        $basicPeople = $this->getPeople();
        $userExpected = $this->getUser();

        $activePeople = $this->getPeople();
        $activePeople->setDisabled(false);
        $userExpected2 = $this->getUser();
        $userExpected2->setActive('active');

        $inactivePeople = $this->getPeople();
        $inactivePeople->setDisabled(true);
        $userExpected3 = $this->getUser();
        $userExpected3->setActive('inactive');

        $peopleWithManager = $this->getPeople();
        $managerId = 1234;
        $manager = new People();
        $reflectionClass = new \ReflectionClass(\App\Entity\User::class);
        $property = $reflectionClass->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($manager, $managerId);
        $peopleWithManager->setSupervisor($manager);

        $userExpected4 = $this->getUser();
        $userExpected4->managerPeopleId = '1234';
        $userExpected4->managerAgileId = 'agileManagerId';

        $managerUserOnAgile = $this->getUser();
        $managerUserOnAgile->peopleId = (string) $managerId;
        $agileUsers['agileManagerId'] = $managerUserOnAgile;

        $basicPeople = $this->getPeople();
        $userExpected5 = $this->getUser();
        $userExpected5->positionCategory = 'ma position category';

        return [
            'Basic people' => [$basicPeople, $userExpected],
            'Active people' => [$activePeople, $userExpected2],
            'Inactive people' => [$inactivePeople, $userExpected3],
            'people with manager in list' => [$peopleWithManager, $userExpected4, $agileUsers],
            'people with position category' => [$basicPeople, $userExpected5],
        ];
    }

    private function getPeople(): People
    {
        $people = new People();
        $people->setEmail('toto@toto.com');

        $reflectionClass = new \ReflectionClass(\App\Entity\User::class);
        $property = $reflectionClass->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($people, 123);

        return $people;
    }

    private function getUser(): User
    {
        $user = new User();
        $user->peopleId = '123';
        $user->email = 'toto@toto.com';

        return $user;
    }
}
