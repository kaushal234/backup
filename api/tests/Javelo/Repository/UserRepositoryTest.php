<?php

declare(strict_types=1);

namespace App\Tests\Javelo\Repository;

use App\Entity\Directory\People;
use App\Javelo\Repository\UserRepository;
use App\Javelo\Resources\User;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UserRepositoryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider peopleAndJaveloUserDataProvider
     */
    public function testSearchJaveloUserConcerned(array $peopleData, array $javeloUserData, bool $expectedResult): void
    {
        $userRepository = new UserRepository(
            $this->prophesize(EntityManagerInterface::class)->reveal(),
        );

        $peopleProphecy = $this->prophesize(People::class);
        $peopleProphecy->getId()->willReturn($peopleData['id']);
        $peopleProphecy->getUsername()->willReturn($peopleData['username']);
        $people = $peopleProphecy->reveal();

        $javeloUser = new User();
        $javeloUser->intranetId = $javeloUserData['intranetId'];
        $javeloUser->userName = $javeloUserData['userName'];

        $result = $userRepository->searchJaveloUserConcerned($people, [$javeloUser]);

        $this->assertSame($javeloUser === $result, $expectedResult);
    }

    public function peopleAndJaveloUserDataProvider(): array
    {
        return [
            'matching_user username' => [
                ['username' => 'pareil', 'id' => 123],
                ['userName' => 'pareil', 'intranetId' => 'pas le meme id'],
                true,
            ],
            'matching_user username and id' => [
                ['username' => 'pareil', 'id' => 123],
                ['userName' => 'pareil', 'intranetId' => '123'],
                true,
            ],
            'matching_user id' => [
                ['username' => 'username', 'id' => 123],
                ['userName' => 'pas le meme username', 'intranetId' => '123'],
                true,
            ],
            'not matching id and username' => [
                ['username' => 'username', 'id' => 123],
                ['userName' => 'pas le meme username', 'intranetId' => 'pas le meme id'],
                false,
            ],
            'not matching id and username with null value' => [
                ['username' => 'username', 'id' => 123],
                ['userName' => null, 'intranetId' => null],
                false,
            ],
        ];
    }

    public function testSearchPeopleConcernedBySynchronization(): void
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['searchPeopleForJaveloSynchronization'])->getMock();

        $peopleRepositoryMock->expects($this->once())->method('searchPeopleForJaveloSynchronization')->with(
            UserRepository::CONTRACT_TYPES,
            UserRepository::EXCLUDE_DIVISIONS,
            UserRepository::EXCLUDE_BUSINESS_UNITS,
            UserRepository::EXCLUDE_PEOPLE,
            UserRepository::DISABLED_AT_THRESHOLD,
            1
        )->willReturn([]);

        $entityManagerProphecy->getRepository(People::class)->shouldBeCalledTimes(1)->willReturn($peopleRepositoryMock);

        $userRepository = new UserRepository($entityManagerProphecy->reveal());

        $result = $userRepository->searchPeopleConcernedBySynchronization(1);

        $this->assertIsArray($result);
    }
}
