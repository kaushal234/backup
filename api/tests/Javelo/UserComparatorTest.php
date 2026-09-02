<?php

declare(strict_types=1);

namespace App\Tests\Javelo;

use App\Entity\Directory\People;
use App\Javelo\Resources\User;
use App\Javelo\UserComparator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UserComparatorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider changesDataProvider
     */
    public function testGetChanges(People $people, ?User $previousJaveloUser, User $updatedJaveloUser, array $expectedChanges): void
    {
        $userComparator = new UserComparator();

        $actualChanges = $userComparator->getChanges($updatedJaveloUser, $previousJaveloUser);

        $this->assertSame($expectedChanges, $actualChanges);
    }

    public function changesDataProvider(): array
    {
        $peopleProphecy = $this->prophesize(People::class);
        $people = $peopleProphecy->reveal();

        $previousJaveloUser1 = new User();
        $previousJaveloUser1->id = 'previousId';
        $previousJaveloUser1->userName = 'oldUsername';

        $updatedJaveloUser1 = new User();
        $updatedJaveloUser1->id = 'previousId';
        $updatedJaveloUser1->userName = 'newUsername';

        $updatedJaveloUser2 = new User();
        $updatedJaveloUser2->id = 'newId';
        $updatedJaveloUser2->userName = 'newUsername';

        $previousWithGender = new User();
        $previousWithGender->gender = 'male';

        $updatedWithEmptyGender = new User();
        $updatedWithEmptyGender->gender = '';

        $updatedWithNullGender = new User();
        $updatedWithNullGender->gender = null;

        $updatedWithValidGender = new User();
        $updatedWithValidGender->gender = 'female';

        $previousWithLocale = new User();
        $previousWithLocale->locale = 'en';

        $updatedWithNewLocale = new User();
        $updatedWithNewLocale->locale = 'fr';

        return [
            'changes with previous user' => [
                'people' => $people,
                'previousJaveloUser' => $previousJaveloUser1,
                'updatedJaveloUser' => $updatedJaveloUser1,
                'expectedChanges' => [
                    'userName' => ['oldUsername', 'newUsername'],
                ],
            ],
            'changes with null previous user' => [
                'people' => $people,
                'previousJaveloUser' => null,
                'updatedJaveloUser' => $updatedJaveloUser2,
                'expectedChanges' => [
                    'id' => [null, 'newId'],
                    'userName' => [null, 'newUsername'],
                ],
            ],
            'gender not updated if previous exists and updated is empty' => [
                'people' => $people,
                'previousJaveloUser' => $previousWithGender,
                'updatedJaveloUser' => $updatedWithEmptyGender,
                'expectedChanges' => [],
            ],
            'gender not updated if previous exists and updated is null' => [
                'people' => $people,
                'previousJaveloUser' => $previousWithGender,
                'updatedJaveloUser' => $updatedWithNullGender,
                'expectedChanges' => [],
            ],
            'gender updated if previous is empty and updated has value' => [
                'people' => $people,
                'previousJaveloUser' => $updatedWithEmptyGender,
                'updatedJaveloUser' => $updatedWithValidGender,
                'expectedChanges' => [
                    'gender' => ['', 'female'],
                ],
            ],
            'gender updated if previous is null and updated has value' => [
                'people' => $people,
                'previousJaveloUser' => new User(), // gender = null by default
                'updatedJaveloUser' => $updatedWithValidGender,
                'expectedChanges' => [
                    'gender' => [null, 'female'],
                ],
            ],
            'locale always ignored' => [
                'people' => $people,
                'previousJaveloUser' => $previousWithLocale,
                'updatedJaveloUser' => $updatedWithNewLocale,
                'expectedChanges' => [],
            ],
        ];
    }
}
