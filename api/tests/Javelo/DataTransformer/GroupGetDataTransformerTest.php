<?php

declare(strict_types=1);

namespace App\Tests\Javelo\DataTransformer;

use App\Javelo\DataTransformer\GroupGetDataTransformer;
use App\Javelo\Repository\GroupRepository;
use PHPUnit\Framework\TestCase;

class GroupGetDataTransformerTest extends TestCase
{
    private GroupGetDataTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new GroupGetDataTransformer();
    }

    /**
     * @dataProvider provideTransformData
     */
    public function testTransform($input, $expected, $expectException = false): void
    {
        if ($expectException) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage("Group data is missing 'id' or 'members'.");
        }

        $result = $this->transformer->transform($input);

        if (!$expectException) {
            $this->assertSame($expected, $result);
        }
    }

    public function provideTransformData(): array
    {
        return [
            'valid data' => [
                'input' => [
                    'id' => 123,
                    'members' => [
                        ['value' => 'user1'],
                        ['value' => 'user2'],
                    ],
                ],
                'expected' => [
                    'id' => 123,
                    'members' => [
                        'user1' => true,
                        'user2' => true,
                    ],
                    GroupRepository::ADD_MEMBERS_KEY => [],
                    GroupRepository::REMOVE_MEMBERS_KEY => [],
                ],
                'expectException' => false,
            ],
            'missing id' => [
                'input' => [
                    'members' => [
                        ['value' => 'user1'],
                    ],
                ],
                'expected' => null,
                'expectException' => true,
            ],
            'missing members' => [
                'input' => [
                    'id' => 123,
                ],
                'expected' => null,
                'expectException' => true,
            ],
            'empty members' => [
                'input' => [
                    'id' => 123,
                    'members' => [],
                ],
                'expected' => [
                    'id' => 123,
                    'members' => [],
                    GroupRepository::ADD_MEMBERS_KEY => [],
                    GroupRepository::REMOVE_MEMBERS_KEY => [],
                ],
                'expectException' => false,
            ],
        ];
    }
}
