<?php

declare(strict_types=1);

namespace App\Tests\Javelo\Repository;

use App\Http\JaveloClient;
use App\Javelo\DataTransformer\GroupGetDataTransformer;
use App\Javelo\Repository\GroupClientRepository;
use App\Javelo\Repository\GroupRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GroupClientRepositoryTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $javeloClientProphecy;
    private ObjectProphecy $getDataTransformerProphecy;
    private GroupClientRepository $groupClientRepository;

    protected function setUp(): void
    {
        $this->javeloClientProphecy = $this->prophesize(JaveloClient::class);
        $this->getDataTransformerProphecy = $this->prophesize(GroupGetDataTransformer::class);
        $this->groupClientRepository = new GroupClientRepository(
            $this->javeloClientProphecy->reveal(),
            $this->getDataTransformerProphecy->reveal()
        );
    }

    public function testGetAllGroupsReturnsTransformedGroups(): void
    {
        $responseContent = json_encode([
            'Resources' => [
                ['displayName' => 'BU_Group1', 'id' => '1'],
                ['displayName' => 'OtherGroup', 'id' => '2'],
                ['displayName' => 'BU_Group2', 'id' => '3'],
            ],
        ]);
        $groupsJsonEmpty = json_encode(['Resources' => []]);

        $responseMock = $this->prophesize(ResponseInterface::class);
        $responseMock->getContent()->shouldBeCalledTimes(2)->willReturn($responseContent, $groupsJsonEmpty);
        $responseMock->getStatusCode()->shouldBeCalledTimes(2)->willReturn(200);

        $this->javeloClientProphecy->doGroupRequest(['startIndex' => 1])->shouldBeCalledTimes(1)->willReturn($responseMock->reveal());
        $this->javeloClientProphecy->doGroupRequest(['startIndex' => 4])->shouldBeCalledTimes(1)->willReturn($responseMock->reveal());

        $this->getDataTransformerProphecy
            ->transform(['displayName' => 'BU_Group1', 'id' => '1'])
            ->willReturn([
                'id' => '1',
                'members' => [],
                GroupRepository::ADD_MEMBERS_KEY => [],
                GroupRepository::REMOVE_MEMBERS_KEY => [],
            ]);

        $this->getDataTransformerProphecy
            ->transform(['displayName' => 'BU_Group2', 'id' => '3'])
            ->willReturn([
                'id' => '3',
                'members' => [],
                GroupRepository::ADD_MEMBERS_KEY => [],
                GroupRepository::REMOVE_MEMBERS_KEY => [],
            ]);

        $groups = $this->groupClientRepository->getAllGroups();

        $this->assertCount(2, $groups);
        $this->assertArrayHasKey('BU_Group1', $groups);
        $this->assertArrayHasKey('BU_Group2', $groups);
    }

    public function testGetAllGroupsHandlesErrorResponse(): void
    {
        $responseInterface = $this->prophesize(ResponseInterface::class);
        $responseInterface->getStatusCode()->shouldBeCalledTimes(2)->willReturn(404);
        $responseInterface->getContent()->shouldBeCalled()->willReturn('Not Found');

        $this->javeloClientProphecy
            ->doGroupRequest(['startIndex' => 1])
            ->willReturn($responseInterface->reveal());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid response on getAllGroups : Not Found');

        $this->groupClientRepository->getAllGroups();
    }

    public function testUpdateGroupWithEmptyMembersDoesNotSendRequest(): void
    {
        $group = [
            'id' => 'group_id',
            'addMembers' => [],
            'removeMembers' => [],
        ];

        $this->javeloClientProphecy
            ->doGroupRequest(Argument::any())
            ->shouldNotBeCalled();

        $this->groupClientRepository->updateGroup($group);
    }

    public function testUpdateGroupSendsRequest(): void
    {
        $group = [
            'id' => 'group_id',
            'addMembers' => ['user123'],
            'removeMembers' => [],
        ];

        $responseInterface = $this->prophesize(ResponseInterface::class);
        $responseInterface->getStatusCode()->willReturn(Response::HTTP_OK);
        $this->javeloClientProphecy
            ->doGroupRequest(['body' => $group], 'group_id', Request::METHOD_PATCH)
            ->willReturn($responseInterface->reveal());

        $this->groupClientRepository->updateGroup($group);

        $this->javeloClientProphecy->doGroupRequest(['body' => $group], 'group_id', Request::METHOD_PATCH)->shouldHaveBeenCalled();
    }
}
