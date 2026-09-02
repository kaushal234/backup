<?php

declare(strict_types=1);

namespace App\Tests\Agile;

use App\Agile\Repository\UserRepository;
use App\Agile\Resources\User;
use App\Agile\UserEventResolver;
use App\Agile\UserSyncContext;
use App\Agile\UserSyncProcessor;
use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserSyncProcessorContextResolutionTest extends KernelTestCase
{
    private UserRepository|MockObject $userRepository;
    private PeopleRepository|MockObject $peopleRepository;
    private UserSyncContext|MockObject $userSyncContext;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->peopleRepository = $this->createMock(PeopleRepository::class);
        $this->userSyncContext = self::getContainer()->get(UserSyncContext::class);
    }

    /**
     * @dataProvider eventResolutionProvider
     */
    public function testResolveSyncContextWithVariousEvents(
        ?User $previousAgileUser,
        People $people,
        array $peopleConcerned,
        ?string $expectedEvent
    ): void {
        $this->userRepository
            ->expects($this->once())
            ->method('getAllUsers')
            ->willReturn([new User()]);

        $this->peopleRepository
            ->expects($this->once())
            ->method('searchAllPeopleIdWithGroup')
            ->willReturn($peopleConcerned);

        $this->userRepository
            ->expects($this->once())
            ->method('searchAgileUserConcerned')
            ->willReturn($previousAgileUser);

        $processor = new UserSyncProcessor(
            $this->userRepository,
            $this->peopleRepository,
            $this->userSyncContext,
            new NullLogger()
        );

        $context = $processor->resolveSyncContext($people);

        if (null === $expectedEvent) {
            $this->assertNull($context);
        } else {
            $this->assertInstanceOf(UserSyncContext::class, $context);
            $this->assertSame($expectedEvent, $context->getResolvedEvent());
        }
    }

    public function eventResolutionProvider(): \Generator
    {
        $activeUser = new User();
        $activeUser->setActive('active');
        $activeUser->peopleId = '123';
        $activeUser->email = 'old@example.com';

        $inactiveUser = new User();
        $inactiveUser->setActive('inactive');
        $inactiveUser->peopleId = '123';

        yield 'User joined (no previous, concerned)' => [
            'previousAgileUser' => null,
            'people' => $this->mockPeople(123, false),
            'peopleConcerned' => [['id' => 123]],
            'expectedEvent' => UserEventResolver::USER_JOINED,
        ];

        yield 'User joined (previous inactive, concerned)' => [
            'previousAgileUser' => $inactiveUser,
            'people' => $this->mockPeople(123, false),
            'peopleConcerned' => [['id' => 123]],
            'expectedEvent' => UserEventResolver::USER_JOINED,
        ];

        yield 'User updated (email changed)' => [
            'previousAgileUser' => $activeUser,
            'people' => $this->mockPeople(123, false, 'new@example.com'),
            'peopleConcerned' => [['id' => 123]],
            'expectedEvent' => UserEventResolver::USER_UPDATED,
        ];

        yield 'User suspended (concerned but inactive)' => [
            'previousAgileUser' => $activeUser,
            'people' => $this->mockPeople(123, true),
            'peopleConcerned' => [['id' => 123]],
            'expectedEvent' => UserEventResolver::USER_SUSPENDED,
        ];

        yield 'User suspended (not concerned but active )' => [
            'previousAgileUser' => $activeUser,
            'people' => $this->mockPeople(123, false),
            'peopleConcerned' => [['id' => 456]],
            'expectedEvent' => UserEventResolver::USER_SUSPENDED,
        ];

        yield 'No event (no change, concerned, active)' => [
            'previousAgileUser' => $activeUser,
            'people' => $this->mockPeople(123, false),
            'peopleConcerned' => [['id' => 123]],
            'expectedEvent' => null,
        ];

        yield 'No event (no change, not concerned, not active)' => [
            'previousAgileUser' => $inactiveUser,
            'people' => $this->mockPeople(123, true),
            'peopleConcerned' => [['id' => 456]],
            'expectedEvent' => null,
        ];
    }

    private function mockPeople(int $id, bool $disabled = false, string $email = 'old@example.com'): People
    {
        $people = $this->createMock(People::class);
        $people->method('getId')->willReturn($id);
        $people->method('isDisabled')->willReturn($disabled);
        $people->method('getEmail')->willReturn($email);

        return $people;
    }
}
