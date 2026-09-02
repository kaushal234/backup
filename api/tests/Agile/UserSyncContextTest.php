<?php

declare(strict_types=1);

namespace App\Tests\Agile;

use App\Agile\Factory\UserFactory;
use App\Agile\Resources\User;
use App\Agile\UserComparator;
use App\Agile\UserEventResolver;
use App\Agile\UserSyncContext;
use App\Entity\Directory\People;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UserSyncContextTest extends TestCase
{
    use ProphecyTrait;

    private $userFactory;
    private $userComparator;

    protected function setUp(): void
    {
        $this->userFactory = $this->prophesize(UserFactory::class);
        $this->userComparator = $this->prophesize(UserComparator::class);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testBuildContext(
        People $apiUser,
        ?User $previousAgileUser,
        bool $isSynchroniseUser,
        User $expectedAgileUserToUpdate,
        array $expectedChanges,
        ?string $expectedEvent
    ): void {
        $this->userFactory
            ->createFromPeople($apiUser, null === $previousAgileUser, [])
            ->willReturn($expectedAgileUserToUpdate);

        $this->userComparator
            ->getChanges($expectedAgileUserToUpdate, $previousAgileUser)
            ->willReturn($expectedChanges);

        $userSyncContext = new UserSyncContext(
            $this->userFactory->reveal(),
            $this->userComparator->reveal()
        );
        $userSyncContext->buildContext($apiUser, $isSynchroniseUser, [], $previousAgileUser);

        $this->assertSame($expectedAgileUserToUpdate, $userSyncContext->getAgileUserToUpdate());
        $this->assertSame($expectedChanges, $userSyncContext->getChanges());
        $this->assertSame($expectedEvent, $userSyncContext->getResolvedEvent());
    }

    /**
     * @return array[]
     */
    public function contextDataProvider(): array
    {
        $apiUser = $this->prophesize(People::class)->reveal();

        $activeUser = $this->prophesize(User::class);
        $activeUser->isActive()->willReturn(true);

        $inactiveUser = $this->prophesize(User::class);
        $inactiveUser->isActive()->willReturn(false);

        $newUser = $this->prophesize(User::class);

        $updatedUser = $this->prophesize(User::class);
        $updatedUser->isActive()->willReturn(true);

        $tests = [];

        $tests['User joined'] = [
            'apiUser' => $apiUser,
            'previousAgileUser' => null,
            'isSynchroniseUser' => true,
            'expectedAgileUserToUpdate' => $newUser->reveal(),
            'expectedChanges' => ['name' => 'changed'],
            'expectedEvent' => UserEventResolver::USER_JOINED,
        ];

        $tests['User updated'] = [
            'apiUser' => $apiUser,
            'previousAgileUser' => $activeUser->reveal(),
            'isSynchroniseUser' => true,
            'expectedAgileUserToUpdate' => $updatedUser->reveal(),
            'expectedChanges' => ['email' => 'updated@example.com'],
            'expectedEvent' => UserEventResolver::USER_UPDATED,
        ];

        $tests['User suspended'] = [
            'apiUser' => $apiUser,
            'previousAgileUser' => $activeUser->reveal(),
            'isSynchroniseUser' => false,
            'expectedAgileUserToUpdate' => $updatedUser->reveal(),
            'expectedChanges' => [],
            'expectedEvent' => UserEventResolver::USER_SUSPENDED,
        ];

        $tests['No event'] = [
            'apiUser' => $apiUser,
            'previousAgileUser' => $inactiveUser->reveal(),
            'isSynchroniseUser' => false,
            'expectedAgileUserToUpdate' => $updatedUser->reveal(),
            'expectedChanges' => [],
            'expectedEvent' => null,
        ];

        return $tests;
    }
}
