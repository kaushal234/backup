<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Http\JaveloClient;
use App\Javelo\DataTransformer\GroupPatchDataTransformer;
use App\Javelo\DataTransformer\UserPatchDataTransformer;
use App\Javelo\DataTransformer\UserPostDataTransformer;
use App\Javelo\Resources\User;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JaveloClientTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider dataSend
     */
    public function testRequest($entry, $expected): void
    {
        $httpClientMockProphecy = $this->prophesize(HttpClientInterface::class);
        $userPatchDataTransformerProphecy = $this->prophesize(UserPatchDataTransformer::class);
        $userPostDataTransformerProphecy = $this->prophesize(UserPostDataTransformer::class);
        $groupPatchDataTransformerProphecy = $this->prophesize(GroupPatchDataTransformer::class);

        $httpClientMockProphecy->request($expected['method'], $expected['url'], $expected['options'])->shouldBeCalled();

        if (\array_key_exists('body', $entry['options']) && Request::METHOD_PATCH === $entry['method']) {
            $userPatchDataTransformerProphecy->transform($entry['options']['body'])->shouldBeCalled()->willReturn([0 => 'see test on dataTransformer::userPatchDataTransformer']);
        }

        if (\array_key_exists('body', $entry['options']) && Request::METHOD_POST === $entry['method']) {
            $userPostDataTransformerProphecy->transform($entry['options']['body'])->shouldBeCalled()->willReturn([0 => 'see test on dataTransformer::userPostDataTransformer']);
        }

        $javeloClient = new JaveloClient($httpClientMockProphecy->reveal(), $userPatchDataTransformerProphecy->reveal(), $userPostDataTransformerProphecy->reveal(), $groupPatchDataTransformerProphecy->reveal());
        $javeloClient->doUserRequest($entry['options'], $entry['id'], $entry['method']);
    }

    public function dataSend(): array
    {
        return [
            'test add Id in url' => [
                'entry' => [
                    'options' => [],
                    'id' => 'javeloIdIsAString',
                    'method' => Request::METHOD_GET],
                'expected' => [
                    'method' => Request::METHOD_GET,
                    'url' => 'Users/javeloIdIsAString',
                    'options' => []],
            ],
            'test get All users' => [
                'entry' => [
                    'options' => [],
                    'id' => null,
                    'method' => Request::METHOD_GET],
                'expected' => [
                    'method' => Request::METHOD_GET,
                    'url' => 'Users',
                    'options' => []],
            ],
            'test get with filter on username' => [
                'entry' => [
                    'options' => [
                        'filter' => 'username eq email.com',
                    ],
                    'id' => null,
                    'method' => Request::METHOD_GET],
                'expected' => [
                    'method' => Request::METHOD_GET,
                    'url' => 'Users?filter=username%20eq%20email.com',
                    'options' => []],
            ],
            'test get with limit users' => [
                'entry' => [
                    'options' => [
                        'count' => 12,
                    ],
                    'id' => null,
                    'method' => Request::METHOD_GET],
                'expected' => [
                    'method' => Request::METHOD_GET,
                    'url' => 'Users?count=12',
                    'options' => []],
            ],
            'test get with startIndex user' => [
                'entry' => [
                    'options' => [
                        'startIndex' => 12,
                    ],
                    'id' => null,
                    'method' => Request::METHOD_GET],
                'expected' => [
                    'method' => Request::METHOD_GET,
                    'url' => 'Users?startIndex=12',
                    'options' => []],
            ],
            'test patch user' => [
                'entry' => [
                    'options' => [
                        'body' => new User(),
                    ],
                    'id' => '12',
                    'method' => Request::METHOD_PATCH],
                'expected' => [
                    'method' => Request::METHOD_PATCH,
                    'url' => 'Users/12',
                    'options' => [
                        'body' => json_encode(['see test on dataTransformer::userPatchDataTransformer']),
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/scim+json',
                        ],
                    ]],
            ],
            'test post a user' => [
                'entry' => [
                    'options' => [
                        'body' => new User(),
                    ],
                    'id' => null,
                    'method' => Request::METHOD_POST],
                'expected' => [
                    'method' => Request::METHOD_POST,
                    'url' => 'Users',
                    'options' => [
                        'body' => json_encode(['see test on dataTransformer::userPostDataTransformer']),
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/scim+json',
                        ],
                    ]],
            ],
        ];
    }

    /**
     * @dataProvider groupDataSend
     */
    public function testGroupRequest($entry, $expected): void
    {
        $httpClientMockProphecy = $this->prophesize(HttpClientInterface::class);
        $groupPatchDataTransformerProphecy = $this->prophesize(GroupPatchDataTransformer::class);

        $httpClientMockProphecy->request($expected['method'], $expected['url'], $expected['options'])->shouldBeCalled();

        if (\array_key_exists('body', $entry['options']) && Request::METHOD_PATCH === $entry['method']) {
            $groupPatchDataTransformerProphecy->transform($entry['options']['body'])
                ->shouldBeCalled()
                ->willReturn(['see test on dataTransformer::groupPatchDataTransformer']);
        }

        $javeloClient = new JaveloClient(
            $httpClientMockProphecy->reveal(),
            $this->prophesize(UserPatchDataTransformer::class)->reveal(),
            $this->prophesize(UserPostDataTransformer::class)->reveal(),
            $groupPatchDataTransformerProphecy->reveal()
        );

        $javeloClient->doGroupRequest($entry['options'], $entry['id'], $entry['method']);
    }

    public function groupDataSend(): array
    {
        return [
            'test get group with startIndex' => [
                'entry' => [
                    'options' => [
                        'startIndex' => 12,
                    ],
                    'id' => null,
                    'method' => Request::METHOD_GET],
                'expected' => [
                    'method' => Request::METHOD_GET,
                    'url' => 'Groups?startIndex=12',
                    'options' => []],
            ],
            'test patch group' => [
                'entry' => [
                    'options' => [
                        'body' => ['group' => 'testGroup'],
                    ],
                    'id' => '12',
                    'method' => Request::METHOD_PATCH],
                'expected' => [
                    'method' => Request::METHOD_PATCH,
                    'url' => 'Groups/12',
                    'options' => [
                        'body' => json_encode(['see test on dataTransformer::groupPatchDataTransformer']),
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/scim+json',
                        ],
                    ]],
            ],
        ];
    }
}
