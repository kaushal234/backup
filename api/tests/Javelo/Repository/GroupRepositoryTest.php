<?php

declare(strict_types=1);

namespace App\Tests\Javelo\Repository;

use App\Javelo\Repository\GroupClientRepository;
use App\Javelo\Repository\GroupRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class GroupRepositoryTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $groupClientRepositoryProphecy;
    private GroupRepository $groupRepository;

    protected function setUp(): void
    {
        $this->groupClientRepositoryProphecy = $this->prophesize(GroupClientRepository::class);
        $this->groupRepository = new GroupRepository(
            $this->groupClientRepositoryProphecy->reveal()
        );
    }

    public function testInitializeGroupsLoadsGroupsOnce(): void
    {
        $groups = [
            'group1' => [
                GroupRepository::ADD_MEMBERS_KEY => [],
                GroupRepository::REMOVE_MEMBERS_KEY => [],
            ],
        ];

        $this->groupClientRepositoryProphecy
            ->getAllGroups()
            ->willReturn($groups)
            ->shouldBeCalledOnce();

        $this->groupRepository->initializeGroups();
        $this->assertSame($groups, $this->groupRepository->getGroups());

        $this->groupRepository->initializeGroups();
    }

    public function testAddUserOnAddMembersListAddsUser(): void
    {
        $javeloGroupName = 'group1';
        $javeloUserId = 'user123';

        $this->groupClientRepositoryProphecy
            ->getAllGroups()
            ->willReturn([
                $javeloGroupName => [
                    GroupRepository::ADD_MEMBERS_KEY => [],
                    GroupRepository::REMOVE_MEMBERS_KEY => [],
                ],
            ]);

        $this->groupRepository->initializeGroups();

        $this->groupRepository->addUserOnAddMembersList($javeloGroupName, $javeloUserId);
        $this->assertContains($javeloUserId, $this->groupRepository->getGroups()[$javeloGroupName][GroupRepository::ADD_MEMBERS_KEY]);
    }

    public function testAddUserOnAddMembersListDoesNotAddExistingUser(): void
    {
        $javeloGroupName = 'group1';
        $javeloUserId = 'user123';

        $this->groupClientRepositoryProphecy
            ->getAllGroups()
            ->willReturn([
                $javeloGroupName => [
                    GroupRepository::ADD_MEMBERS_KEY => [$javeloUserId],
                    GroupRepository::REMOVE_MEMBERS_KEY => [],
                ],
            ]);

        $this->groupRepository->initializeGroups();

        $this->groupRepository->addUserOnAddMembersList($javeloGroupName, $javeloUserId);
        $this->assertCount(1, $this->groupRepository->getGroups()[$javeloGroupName][GroupRepository::ADD_MEMBERS_KEY]);
    }

    public function testAddUserOnRemoveMembersListAddsUser(): void
    {
        $javeloGroupName = 'group1';
        $javeloUserId = 'user456';

        $this->groupClientRepositoryProphecy
            ->getAllGroups()
            ->willReturn([
                $javeloGroupName => [
                    GroupRepository::ADD_MEMBERS_KEY => [],
                    GroupRepository::REMOVE_MEMBERS_KEY => [],
                ],
            ]);

        $this->groupRepository->initializeGroups();

        $this->groupRepository->addUserOnRemoveMembersList($javeloGroupName, $javeloUserId);
        $this->assertContains($javeloUserId, $this->groupRepository->getGroups()[$javeloGroupName][GroupRepository::REMOVE_MEMBERS_KEY]);
    }

    public function testAddUserOnRemoveMembersListDoesNotAddExistingUser(): void
    {
        $javeloGroupName = 'group1';
        $javeloUserId = 'user456';

        $this->groupClientRepositoryProphecy
            ->getAllGroups()
            ->willReturn([
                $javeloGroupName => [
                    GroupRepository::ADD_MEMBERS_KEY => [],
                    GroupRepository::REMOVE_MEMBERS_KEY => [$javeloUserId],
                ],
            ]);

        $this->groupRepository->initializeGroups();

        $this->groupRepository->addUserOnRemoveMembersList($javeloGroupName, $javeloUserId);
        $this->assertCount(1, $this->groupRepository->getGroups()[$javeloGroupName][GroupRepository::REMOVE_MEMBERS_KEY]);
    }

    public function testAddMissingGroupAddsGroup(): void
    {
        $this->groupRepository->addMissingGroup('BU_SALES', 'Sales', 'EMEA');

        $this->assertSame(
            ['BU_SALES' => ['businessUnit' => 'Sales', 'division' => 'EMEA']],
            $this->groupRepository->getMissingGroups()
        );
    }

    public function testAddMissingGroupDoesNotOverwriteExistingGroup(): void
    {
        $this->groupRepository->addMissingGroup('BU_SALES', 'Sales', 'EMEA');
        $this->groupRepository->addMissingGroup('BU_SALES', 'Sales Other', 'APAC');

        $this->assertSame(
            ['BU_SALES' => ['businessUnit' => 'Sales', 'division' => 'EMEA']],
            $this->groupRepository->getMissingGroups()
        );
    }

    public function testAddMissingGroupWithNullDivision(): void
    {
        $this->groupRepository->addMissingGroup('BU_HR', 'HR', null);

        $this->assertSame(
            ['BU_HR' => ['businessUnit' => 'HR', 'division' => null]],
            $this->groupRepository->getMissingGroups()
        );
    }

    public function testGetMissingGroupsReturnsEmptyArrayByDefault(): void
    {
        $this->assertSame([], $this->groupRepository->getMissingGroups());
    }
}
