<?php

declare(strict_types=1);

namespace App\Tests\Agile;

use App\Agile\Resources\User;
use App\Agile\UserEventResolver;
use PHPUnit\Framework\TestCase;

class UserEventResolverTest extends TestCase
{
    /**
     * @dataProvider userEventProvider
     */
    public function testUserEvents(?User $previousUserOnAgile, array $changes, bool $isSynchronized, ?string $expected): void
    {
        $resolver = new UserEventResolver(
            $previousUserOnAgile,
            $changes,
            $isSynchronized
        );

        $this->assertSame($expected, $resolver->getResolvedEvent());
    }

    public function userEventProvider(): array
    {
        $activeUser = new User();
        $activeUser->setActive('active');

        $inactiveUser = new User();
        $inactiveUser->setActive('inactive');

        return [
            'user joined' => [
                'previousUserOnAgile' => null,
                'changes' => [],
                'isSynchronized' => true,
                'expected' => UserEventResolver::USER_JOINED,
            ],
            'user updated' => [
                'previousUserOnAgile' => $activeUser,
                'changes' => ['name' => 'Updated Name'],
                'isSynchronized' => true,
                'expected' => UserEventResolver::USER_UPDATED,
            ],
            'user suspended' => [
                'previousUserOnAgile' => $activeUser,
                'changes' => [],
                'isSynchronized' => false,
                'expected' => UserEventResolver::USER_SUSPENDED,
            ],
            'people have no changes' => [
                'previousUserOnAgile' => $activeUser,
                'changes' => [],
                'isSynchronized' => true,
                'expected' => null,
            ],
            'no event with inactive previous user and not synchronized' => [
                'previousUserOnAgile' => $inactiveUser,
                'changes' => [],
                'isSynchronized' => false,
                'expected' => null,
            ],
            'inactive previous user become active' => [
                'previousUserOnAgile' => $inactiveUser,
                'changes' => [
                    'active' => true,
                ],
                'isSynchronized' => true,
                'expected' => UserEventResolver::USER_JOINED,
            ],
            'People from synchronized to not synchronize' => [
                'previousUserOnAgile' => $activeUser,
                'changes' => [
                    'active' => true,
                ],
                'isSynchronized' => false,
                'expected' => UserEventResolver::USER_SUSPENDED,
            ],
        ];
    }
}
