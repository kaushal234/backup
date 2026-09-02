<?php

declare(strict_types=1);

namespace App\Tests\Javelo\EventListener;

use App\Javelo\Event\GroupUpdateEvent;
use App\Javelo\EventListener\GroupUpdateListener;
use App\Javelo\Notifier\Notifier;
use App\Javelo\Repository\GroupClientRepository;
use App\Javelo\Repository\GroupRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class GroupUpdateListenerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $groupClientRepositoryProphecy;
    private ObjectProphecy $groupRepositoryProphecy;
    private LoggerInterface|ObjectProphecy $loggerProphecy;
    private ObjectProphecy $notifierProphecy;
    private GroupUpdateListener $listener;

    protected function setUp(): void
    {
        $this->groupClientRepositoryProphecy = $this->prophesize(GroupClientRepository::class);
        $this->groupRepositoryProphecy = $this->prophesize(GroupRepository::class);
        $this->loggerProphecy = $this->prophesize(LoggerInterface::class);
        $this->notifierProphecy = $this->prophesize(Notifier::class);

        $this->listener = new GroupUpdateListener(
            $this->groupClientRepositoryProphecy->reveal(),
            $this->groupRepositoryProphecy->reveal(),
            $this->loggerProphecy->reveal(),
            $this->notifierProphecy->reveal()
        );
    }

    public function testInvokeWhenGroupsHaveMembersToUpdate(): void
    {
        $groups = [
            [
                GroupRepository::ADD_MEMBERS_KEY => ['user1'],
                GroupRepository::REMOVE_MEMBERS_KEY => [],
            ],
            [
                GroupRepository::ADD_MEMBERS_KEY => [],
                GroupRepository::REMOVE_MEMBERS_KEY => ['user2'],
            ],
        ];

        $eventProphecy = $this->prophesize(GroupUpdateEvent::class);
        $eventProphecy->shouldSendMissingGroupMail()->willReturn(false);
        $event = $eventProphecy->reveal();

        $this->groupRepositoryProphecy->getGroups()->willReturn($groups)->shouldBeCalled();
        $this->groupClientRepositoryProphecy->updateGroup($groups[0])->shouldBeCalled();
        $this->groupClientRepositoryProphecy->updateGroup($groups[1])->shouldBeCalled();

        $this->notifierProphecy->sendCreationGroupsRequest()->shouldNotBeCalled();
        $this->loggerProphecy->error()->shouldNotBeCalled();

        $this->listener->__invoke($event);
    }

    public function testInvokeWhenGroupsHaveNoMembersToUpdate(): void
    {
        $groups = [
            [
                GroupRepository::ADD_MEMBERS_KEY => [],
                GroupRepository::REMOVE_MEMBERS_KEY => [],
            ],
        ];

        $eventProphecy = $this->prophesize(GroupUpdateEvent::class);
        $eventProphecy->shouldSendMissingGroupMail()->willReturn(false);
        $event = $eventProphecy->reveal();

        $this->groupRepositoryProphecy->getGroups()->willReturn($groups)->shouldBeCalled();
        $this->groupClientRepositoryProphecy->updateGroup()->shouldNotBeCalled();

        $this->notifierProphecy->sendCreationGroupsRequest()->shouldNotBeCalled();
        $this->loggerProphecy->error()->shouldNotBeCalled();

        $this->listener->__invoke($event);
    }

    public function testInvokeWithException(): void
    {
        $groups = [
            'groupA' => [
                GroupRepository::ADD_MEMBERS_KEY => ['user1'],
                GroupRepository::REMOVE_MEMBERS_KEY => ['user2'],
            ],
        ];

        $eventProphecy = $this->prophesize(GroupUpdateEvent::class);
        $eventProphecy->shouldSendMissingGroupMail()->willReturn(false);
        $event = $eventProphecy->reveal();

        $this->groupRepositoryProphecy->getGroups()->willReturn($groups)->shouldBeCalled();

        $this->groupClientRepositoryProphecy->updateGroup($groups['groupA'])
            ->willThrow(new \Exception('Update failed'));

        $this->loggerProphecy->error('Something went wrong when update groups on Javelo: {error}', [
            'error' => 'Update failed',
        ])->shouldBeCalled();

        $this->listener->__invoke($event);
    }

    public function testInvokeSendsMissingGroupMailWhenFlagIsTrueAndGroupsAreMissing(): void
    {
        $groups = [
            [
                GroupRepository::ADD_MEMBERS_KEY => [],
                GroupRepository::REMOVE_MEMBERS_KEY => [],
            ],
        ];
        $missingGroups = [
            'BU_SALES' => ['businessUnit' => 'Sales', 'division' => 'EMEA'],
        ];

        $eventProphecy = $this->prophesize(GroupUpdateEvent::class);
        $eventProphecy->shouldSendMissingGroupMail()->willReturn(true);
        $event = $eventProphecy->reveal();

        $this->groupRepositoryProphecy->getGroups()->willReturn($groups)->shouldBeCalled();
        $this->groupRepositoryProphecy->getMissingGroups()->willReturn($missingGroups)->shouldBeCalled();

        $this->notifierProphecy->sendCreationGroupsRequest($missingGroups)->shouldBeCalledOnce();
        $this->loggerProphecy->error()->shouldNotBeCalled();

        $this->listener->__invoke($event);
    }

    public function testInvokeDoesNotSendMissingGroupMailWhenFlagIsTrueButNoGroupsAreMissing(): void
    {
        $groups = [
            [
                GroupRepository::ADD_MEMBERS_KEY => [],
                GroupRepository::REMOVE_MEMBERS_KEY => [],
            ],
        ];

        $eventProphecy = $this->prophesize(GroupUpdateEvent::class);
        $eventProphecy->shouldSendMissingGroupMail()->willReturn(true);
        $event = $eventProphecy->reveal();

        $this->groupRepositoryProphecy->getGroups()->willReturn($groups)->shouldBeCalled();
        $this->groupRepositoryProphecy->getMissingGroups()->willReturn([])->shouldBeCalled();

        $this->notifierProphecy->sendCreationGroupsRequest()->shouldNotBeCalled();
        $this->loggerProphecy->error()->shouldNotBeCalled();

        $this->listener->__invoke($event);
    }

    public function testInvokeDoesNotSendMissingGroupMailWhenFlagIsFalse(): void
    {
        $groups = [
            [
                GroupRepository::ADD_MEMBERS_KEY => [],
                GroupRepository::REMOVE_MEMBERS_KEY => [],
            ],
        ];

        $eventProphecy = $this->prophesize(GroupUpdateEvent::class);
        $eventProphecy->shouldSendMissingGroupMail()->willReturn(false);
        $event = $eventProphecy->reveal();

        $this->groupRepositoryProphecy->getGroups()->willReturn($groups)->shouldBeCalled();
        $this->groupRepositoryProphecy->getMissingGroups()->shouldNotBeCalled();

        $this->notifierProphecy->sendCreationGroupsRequest()->shouldNotBeCalled();
        $this->loggerProphecy->error()->shouldNotBeCalled();

        $this->listener->__invoke($event);
    }

    public function testInvokeLogsErrorWhenSendingMissingGroupMailFails(): void
    {
        $groups = [
            [
                GroupRepository::ADD_MEMBERS_KEY => [],
                GroupRepository::REMOVE_MEMBERS_KEY => [],
            ],
        ];
        $missingGroups = [
            'BU_SALES' => ['businessUnit' => 'Sales', 'division' => 'EMEA'],
        ];

        $eventProphecy = $this->prophesize(GroupUpdateEvent::class);
        $eventProphecy->shouldSendMissingGroupMail()->willReturn(true);
        $event = $eventProphecy->reveal();

        $this->groupRepositoryProphecy->getGroups()->willReturn($groups)->shouldBeCalled();
        $this->groupRepositoryProphecy->getMissingGroups()->willReturn($missingGroups)->shouldBeCalled();

        $this->notifierProphecy->sendCreationGroupsRequest($missingGroups)
            ->willThrow(new \Exception('Mailer error'));

        $this->loggerProphecy->error('Something went wrong when sending missing group creation email: {error}', [
            'error' => 'Mailer error',
        ])->shouldBeCalledOnce();

        $this->listener->__invoke($event);
    }
}
