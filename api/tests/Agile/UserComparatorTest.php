<?php

declare(strict_types=1);

namespace App\Tests\Agile;

use App\Agile\Resources\User;
use App\Agile\UserComparator;
use PHPUnit\Framework\TestCase;

class UserComparatorTest extends TestCase
{
    private UserComparator $userComparator;

    protected function setUp(): void
    {
        $this->userComparator = new UserComparator();
    }

    public function changesDataProvider(): array
    {
        $activeUser = new User();
        $activeUser->managerAgileId = 'managerId1';
        $activeUser->peopleId = 'peopleId1';
        $activeUser->jobTitle = 'Developer';
        $activeUser->firstName = 'John';
        $activeUser->lastName = 'Doe';
        $activeUser->managerPeopleId = 'managerPeopleId1';
        $activeUser->timeZone = 'America/New_York';
        $activeUser->languageCode = 'en';
        $activeUser->position = 'Position1';
        $activeUser->department = 'Department1';
        $activeUser->region = 'Region1';
        $activeUser->businessunit = 'BusinessUnit1';
        $activeUser->subdivision = 'Subdivision1';
        $activeUser->division = 'Division1';
        $activeUser->address = '123 Main St';
        $activeUser->contracttype = 'Full-time';
        $activeUser->street1 = '123 Main St';
        $activeUser->street2 = 'Apt 4B';
        $activeUser->city = 'Cityville';
        $activeUser->country = 'Countryland';
        $activeUser->zipcode = '12345';
        $activeUser->email = 'john.doe@example.com';
        $activeUser->isNew = true;
        $activeUser->setStartDate('2024-01-01');
        $activeUser->setActive('active');

        $inactiveUser = new User();
        $inactiveUser->managerAgileId = 'managerId2';
        $inactiveUser->peopleId = 'peopleId2';
        $inactiveUser->jobTitle = 'Senior Developer';
        $inactiveUser->firstName = 'Jane';
        $inactiveUser->lastName = 'Smith';
        $inactiveUser->managerPeopleId = 'managerPeopleId2';
        $inactiveUser->timeZone = 'Europe/London';
        $inactiveUser->languageCode = 'fr';
        $inactiveUser->position = 'Position2';
        $inactiveUser->department = 'Department2';
        $inactiveUser->region = 'Region2';
        $inactiveUser->businessunit = 'BusinessUnit2';
        $inactiveUser->subdivision = 'Subdivision2';
        $inactiveUser->division = 'Division2';
        $inactiveUser->address = '456 Elm St';
        $inactiveUser->contracttype = 'Part-time';
        $inactiveUser->street1 = '456 Elm St';
        $inactiveUser->street2 = 'Suite 5A';
        $inactiveUser->city = 'Townsville';
        $inactiveUser->country = 'Nationland';
        $inactiveUser->zipcode = '67890';
        $inactiveUser->email = 'jane.smith@example.com';
        $inactiveUser->isNew = false;
        $inactiveUser->setStartDate('2024-02-01');
        $inactiveUser->setActive('inactive');

        $user3 = clone $activeUser;
        $user3->setStartDate('2095-01-01');
        $user3->loginMethod = 'avec une clé';
        $user3->timeZone = 'Bidon';

        $user4 = clone $activeUser;
        $user4->setActive('inactive');

        $user5 = clone $inactiveUser;
        $user5->setActive('active');

        $user6 = new User();
        $user6->email = 'john.doe@example.com';

        $sameEmailDifferentCase = clone $user6;
        $sameEmailDifferentCase->email = 'John.Doe@Example.com';

        return [
            'agile user update' => [
                'previousAgileUser' => $activeUser,
                'updatedAgileUser' => $inactiveUser,
                'expectedChanges' => [
                    'managerAgileId' => ['managerId1', 'managerId2'],
                    'peopleId' => ['peopleId1', 'peopleId2'],
                    'jobTitle' => ['Developer', 'Senior Developer'],
                    'firstName' => ['John', 'Jane'],
                    'lastName' => ['Doe', 'Smith'],
                    'managerPeopleId' => ['managerPeopleId1', 'managerPeopleId2'],
                    'languageCode' => ['en', 'fr'],
                    'position' => ['Position1', 'Position2'],
                    'department' => ['Department1', 'Department2'],
                    'region' => ['Region1', 'Region2'],
                    'businessunit' => ['BusinessUnit1', 'BusinessUnit2'],
                    'subdivision' => ['Subdivision1', 'Subdivision2'],
                    'division' => ['Division1', 'Division2'],
                    'address' => ['123 Main St', '456 Elm St'],
                    'contracttype' => ['Full-time', 'Part-time'],
                    'street1' => ['123 Main St', '456 Elm St'],
                    'street2' => ['Apt 4B', 'Suite 5A'],
                    'city' => ['Cityville', 'Townsville'],
                    'country' => ['Countryland', 'Nationland'],
                    'zipcode' => ['12345', '67890'],
                    'email' => ['john.doe@example.com', 'jane.smith@example.com'],
                    'isNew' => [true, false],
                    'active' => [true, false],
                ],
            ],
            'user come inactive' => [
                'previousAgileUser' => $activeUser,
                'updatedAgileUser' => $user4,
                'expectedChanges' => [
                    'active' => [
                        0 => true,
                        1 => false,
                    ],
                ],
            ],
            'user come active' => [
                'previousAgileUser' => $inactiveUser,
                'updatedAgileUser' => $user5,
                'expectedChanges' => [
                    'active' => [
                        0 => false,
                        1 => true,
                    ],
                ],
            ],
            'no changes' => [
                'previousAgileUser' => $activeUser,
                'updatedAgileUser' => $activeUser,
                'expectedChanges' => [],
            ],
            'ignored values' => [
                'previousAgileUser' => $activeUser,
                'updatedAgileUser' => $user3,
                'expectedChanges' => [],
            ],
            'email case insensitive' => [
                'previousAgileUser' => $user6,
                'updatedAgileUser' => $sameEmailDifferentCase,
                'expectedChanges' => [],
            ],
        ];
    }

    /**
     * @dataProvider changesDataProvider
     */
    public function testGetChanges(User $previousAgileUser, User $updatedAgileUser, array $expectedChanges): void
    {
        $changes = $this->userComparator->getChanges($updatedAgileUser, $previousAgileUser);
        $this->assertSame($expectedChanges, $changes);

        $reflection = new \ReflectionClass(UserComparator::class);
        $ignoredValues = $reflection->getConstant('IGNORED_VALUES');

        foreach ($changes as $property => $change) {
            $this->assertNotContains($property, $ignoredValues, \sprintf('The property "%s" should be ignored', $property));
        }
    }
}
