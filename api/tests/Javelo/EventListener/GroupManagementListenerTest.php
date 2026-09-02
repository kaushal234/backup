<?php

declare(strict_types=1);

namespace App\Tests\Javelo\EventListener;

use App\Javelo\Event\GroupManagementEvent;
use App\Javelo\EventListener\GroupManagementListener;
use App\Javelo\Repository\GroupRepository;
use App\Javelo\Resources\User;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class GroupManagementListenerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $groupRepositoryProphecy;
    private LoggerInterface|ObjectProphecy $loggerProphecy;
    private GroupManagementListener $listener;

    protected function setUp(): void
    {
        $this->groupRepositoryProphecy = $this->prophesize(GroupRepository::class);
        $this->loggerProphecy = $this->prophesize(LoggerInterface::class);
        $this->listener = new GroupManagementListener(
            $this->groupRepositoryProphecy->reveal(),
            $this->loggerProphecy->reveal(),
        );
    }

    public function testInvokeWhenGroupDoesNotExist(): void
    {
        $javeloUser = new User();
        $javeloUser->id = 'user123';
        $javeloUser->userName = 'username123';
        $javeloUser->businessUnit = 'Sales';

        $event = new GroupManagementEvent($javeloUser);

        $this->groupRepositoryProphecy->initializeGroups()->shouldBeCalledOnce();
        $this->groupRepositoryProphecy->getGroups()->willReturn(['BU_HR' => ['members' => []]]);

        $this->groupRepositoryProphecy->addMissingGroup('BU_SALES', 'Sales', $javeloUser->division)->shouldBeCalledOnce();

        $this->loggerProphecy->error(
            'Something went wrong when controlling group for {business_unit} for {javelo_username} on Javelo: {error}',
            [
                'javelo_username' => 'username123',
                'business_unit' => 'Sales',
                'error' => 'Group does not exist, please create BU_SALES',
            ]
        )->shouldBeCalledOnce();

        $this->listener->__invoke($event);
    }

    public function testInvokeWhenUserNeedsToBeAddedToGroup(): void
    {
        $javeloUser = new User();
        $javeloUser->id = 'user123';
        $javeloUser->businessUnit = 'Marketing';

        $event = new GroupManagementEvent($javeloUser);

        $this->groupRepositoryProphecy->initializeGroups()->shouldBeCalledOnce();
        $this->groupRepositoryProphecy->getGroups()->willReturn([
            'BU_MARKETING' => ['members' => []],
            'BU_HR' => ['members' => ['user123' => true]],
        ]);

        $this->groupRepositoryProphecy->addUserOnAddMembersList('BU_MARKETING', 'user123')->shouldBeCalledOnce();
        $this->groupRepositoryProphecy->addUserOnRemoveMembersList('BU_HR', 'user123')->shouldBeCalledOnce();

        $this->loggerProphecy->error()->shouldNotBeCalled();

        $this->listener->__invoke($event);
    }

    public function testInvokeWhenUserAlreadyInCorrectGroup(): void
    {
        $javeloUser = new User();
        $javeloUser->id = 'user123';
        $javeloUser->businessUnit = 'Marketing';

        $event = new GroupManagementEvent($javeloUser);

        $this->groupRepositoryProphecy->initializeGroups()->shouldBeCalledOnce();
        $this->groupRepositoryProphecy->getGroups()->willReturn([
            'BU_MARKETING' => ['members' => ['user123' => true]],
            'BU_HR' => ['members' => ['user456' => true]],
        ]);

        $this->groupRepositoryProphecy->addUserOnAddMembersList()->shouldNotBeCalled();
        $this->groupRepositoryProphecy->addUserOnRemoveMembersList()->shouldNotBeCalled();

        $this->loggerProphecy->error()->shouldNotBeCalled();

        $this->listener->__invoke($event);
    }

    public function testInvokeWhenUserIdIsNull(): void
    {
        $javeloUser = new User();
        $javeloUser->id = null;
        $javeloUser->userName = 'username123';
        $javeloUser->businessUnit = 'Marketing';

        $event = new GroupManagementEvent($javeloUser);

        $this->groupRepositoryProphecy->initializeGroups()->shouldBeCalledOnce();
        $this->groupRepositoryProphecy->getGroups()->willReturn([
            'BU_MARKETING' => ['members' => []],
            'BU_HR' => ['members' => []],
        ]);

        $this->loggerProphecy->error(
            'Something went wrong when controlling group for {business_unit} for {javelo_username} on Javelo: {error}',
            [
                'javelo_username' => 'username123',
                'business_unit' => 'Marketing',
                'error' => 'User ID is null after user creation.',
            ]
        )->shouldBeCalledOnce();

        $this->listener->__invoke($event);
    }

    public function testInvokeWhenUserIsExcludedFromSynchronization(): void
    {
        $javeloUser = new User();
        $javeloUser->externalId = '13934';
        $javeloUser->userName = 'Mr le comte';
        $javeloUser->position = 'High very high';

        $event = new GroupManagementEvent($javeloUser);

        $this->groupRepositoryProphecy->initializeGroups()->shouldNotBeCalled();
        $this->groupRepositoryProphecy->addUserOnAddMembersList()->shouldNotBeCalled();
        $this->groupRepositoryProphecy->addUserOnRemoveMembersList()->shouldNotBeCalled();
        $this->loggerProphecy->error()->shouldNotBeCalled();

        $this->listener->__invoke($event);
    }

    /**
     * @dataProvider businessUnitNameProvider
     */
    public function testTransformBusinessUnitNameToGroupNameWithReflection(string $input, string $expected): void
    {
        $listener = new GroupManagementListener(
            $this->createMock(GroupRepository::class),
            $this->createMock(LoggerInterface::class),
        );

        $reflection = new \ReflectionClass($listener);
        $method = $reflection->getMethod('transformBusinessUnitNameToGroupName');
        $method->setAccessible(true);

        $result = $method->invoke($listener, $input);
        $this->assertSame($expected, $result);
    }

    public function businessUnitNameProvider(): array
    {
        return [
            'simple name' => ['GSE', 'BU_GSE'],
            'spaces and parentheses' => ['GSE (TLD & AERO)', 'BU_GSE_TLD_AND_AERO'],
            'slashes and special chars' => ['GSE / TLD & AÉRO!', 'BU_GSE_TLD_AND_AERO'],
            'multiple separators' => ['GSE - TLD / AERO & Co.', 'BU_GSE_TLD_AERO_AND_CO'],
            'extra spaces and tabs' => ['  GSE  TLD   AERO  ', 'BU_GSE_TLD_AERO'],
            'dots and underscores' => ['GSE.TLD_AERO', 'BU_GSE_TLD_AERO'],
            'accents and unicode' => ['Développement Régional & Europe', 'BU_DEVELOPPEMENT_REGIONAL_AND_EUROPE'],
            'symbols and numbers' => ['R&D (2023) - Phase #2', 'BU_R_AND_D_2023_PHASE_2'],
            'already formatted input' => ['BU_GSE_TLD', 'BU_BU_GSE_TLD'],
        ];
    }
}
