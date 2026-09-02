<?php

declare(strict_types=1);

namespace App\Tests\Javelo\DataTransformer;

use App\Javelo\DataTransformer\GroupPatchDataTransformer;
use App\Javelo\Repository\GroupRepository;
use PHPUnit\Framework\TestCase;

class GroupPatchDataTransformerTest extends TestCase
{
    private GroupPatchDataTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new GroupPatchDataTransformer();
    }

    /**
     * @dataProvider provideTransformData
     */
    public function testTransform(array $input, ?array $expected, bool $expectException): void
    {
        if ($expectException) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('No members found to add or remove');
        }

        $result = $this->transformer->transform($input);

        if (!$expectException) {
            $this->assertSame($expected, $result);
        }
    }

    public function provideTransformData(): array
    {
        return [
            'add and remove members' => [
                'input' => [
                    GroupRepository::ADD_MEMBERS_KEY => ['user1', 'user2'],
                    GroupRepository::REMOVE_MEMBERS_KEY => ['user3', 'user4'],
                ],
                'expected' => [
                    'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
                    'Operations' => [
                        [
                            'op' => 'remove',
                            'path' => 'members',
                            'value' => [
                                ['value' => 'user3'],
                                ['value' => 'user4'],
                            ],
                        ],
                        [
                            'op' => 'add',
                            'path' => 'members',
                            'value' => [
                                ['value' => 'user1'],
                                ['value' => 'user2'],
                            ],
                        ],
                    ],
                ],
                'expectException' => false,
            ],
            'only add members' => [
                'input' => [
                    GroupRepository::ADD_MEMBERS_KEY => ['user1', 'user2'],
                    GroupRepository::REMOVE_MEMBERS_KEY => [],
                ],
                'expected' => [
                    'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
                    'Operations' => [
                        [
                            'op' => 'add',
                            'path' => 'members',
                            'value' => [
                                ['value' => 'user1'],
                                ['value' => 'user2'],
                            ],
                        ],
                    ],
                ],
                'expectException' => false,
            ],
            'only remove members' => [
                'input' => [
                    GroupRepository::ADD_MEMBERS_KEY => [],
                    GroupRepository::REMOVE_MEMBERS_KEY => ['user3', 'user4'],
                ],
                'expected' => [
                    'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
                    'Operations' => [
                        [
                            'op' => 'remove',
                            'path' => 'members',
                            'value' => [
                                ['value' => 'user3'],
                                ['value' => 'user4'],
                            ],
                        ],
                    ],
                ],
                'expectException' => false,
            ],
            'no members to add or remove' => [
                'input' => [
                    GroupRepository::ADD_MEMBERS_KEY => [],
                    GroupRepository::REMOVE_MEMBERS_KEY => [],
                ],
                'expected' => null,
                'expectException' => true,
            ],
        ];
    }
}
