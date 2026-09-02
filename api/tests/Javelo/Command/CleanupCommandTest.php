<?php

declare(strict_types=1);

namespace App\Tests\Javelo\Command;

use App\Entity\Directory\People;
use App\Javelo\Command\CleanupCommand;
use App\Javelo\Event\GroupManagementEvent;
use App\Javelo\Event\GroupUpdateEvent;
use App\Javelo\Event\UserCreatedEvent;
use App\Javelo\Event\UserUpdatedEvent;
use App\Javelo\Factory\UserFactory;
use App\Javelo\Repository\UserClientRepository;
use App\Javelo\Repository\UserRepository;
use App\Javelo\Resources\User;
use App\Javelo\UserComparator;
use App\Repository\Directory\PeopleRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CleanupCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'api:human-resources:javelo:cleanup';

    private Application $application;

    /** @var MockObject|UserClientRepository */
    private $userClientRepositoryMock;

    /** @var MockObject|UserRepository */
    private $userRepositoryMock;

    /** @var MockObject|PeopleRepository */
    private $peopleRepositoryMock;

    private UserComparator $userComparator;

    private UserFactory $userFactory;

    /** @var ObjectProphecy|EventDispatcherInterface */
    private $eventDispatcherProphecy;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);

        $this->userClientRepositoryMock = $this->getMockBuilder(UserClientRepository::class)->disableOriginalConstructor()->onlyMethods(['getAllUsers'])->getMock();
        $this->userRepositoryMock = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->onlyMethods(['searchPeopleWithAclAuthJavelo', 'searchJaveloUserConcerned'])->getMock();
        $this->peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findAll'])->getMock();

        $this->userComparator = new UserComparator();
        $this->userFactory = new UserFactory();
        $this->eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);

        $this->application->addCommand(new CleanupCommand(
            $this->userClientRepositoryMock,
            $this->userRepositoryMock,
            $this->peopleRepositoryMock,
            $this->userComparator,
            $this->userFactory,
            $this->eventDispatcherProphecy->reveal()
        ));
    }

    /**
     * @dataProvider commandCases
     */
    public function testExecute(array $javeloUsers, array $peopleConcernedId, array $allPeople, int $expectedCreated, int $expectedUpdated): void
    {
        $this->userClientRepositoryMock->expects($this->once())->method('getAllUsers')->willReturn($javeloUsers);
        $this->userRepositoryMock
            ->expects($this->once())
            ->method('searchPeopleWithAclAuthJavelo')
            ->willReturn($peopleConcernedId);
        $this->peopleRepositoryMock->expects($this->once())->method('findAll')->willReturn($allPeople);

        $this->userRepositoryMock
            ->expects($this->exactly(5))
            ->method('searchJaveloUserConcerned')
            ->withConsecutive(
                [$this->callback(static fn ($people) => $people instanceof People && 1 === $people->getId()), $javeloUsers], // userToActivate
                [$this->callback(static fn ($people) => $people instanceof People && 2 === $people->getId()), $javeloUsers], // userToReActivate
                [$this->callback(static fn ($people) => $people instanceof People && 3 === $people->getId()), $javeloUsers], // userToUpdate
                [$this->callback(static fn ($people) => $people instanceof People && 4 === $people->getId()), $javeloUsers], // userToDeactivate
                [$this->callback(static fn ($people) => $people instanceof People && 5 === $people->getId()), $javeloUsers], // userToDoNothing
            )
            ->willReturnOnConsecutiveCalls(
                null,     // userToActivate -> create
                $javeloUsers[0],  // userToReActivate -> update (reactivation)
                $javeloUsers[1],  // userToUpdate -> update (data change)
                $javeloUsers[2],  // userToDeactivate -> update (deactivation)
                $javeloUsers[3],  // userToDoNothing -> SKIP (inactive + no ACL)
            )
        ;

        // 1 create (userToActivate)
        $this->eventDispatcherProphecy->dispatch(Argument::type(UserCreatedEvent::class))->shouldBeCalledTimes($expectedCreated);

        // 3 updates (reActivate, update data, deactivate)
        $this->eventDispatcherProphecy->dispatch(Argument::type(UserUpdatedEvent::class))->shouldBeCalledTimes($expectedUpdated);

        // 3 group management events (only for active javelo users after sync)
        $this->eventDispatcherProphecy->dispatch(Argument::type(GroupManagementEvent::class))->shouldBeCalledTimes(3);

        $this->eventDispatcherProphecy
            ->dispatch(Argument::that(static fn ($event) => $event instanceof GroupUpdateEvent && true === $event->shouldSendMissingGroupMail()))
            ->shouldBeCalledOnce();

        $command = $this->application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString("$expectedCreated people created on javelo.", $output);
        $this->assertStringContainsString("$expectedUpdated people updated on javelo.", $output);
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function commandCases(): array
    {
        // ===== Users already existing on Javelo =====

        $javeloUserToReActivate = new User();
        $javeloUserToReActivate->id = 'javelo-reactivate';
        $javeloUserToReActivate->active = false;

        $javeloUserToUpdate = new User();
        $javeloUserToUpdate->id = 'javelo-update';
        $javeloUserToUpdate->active = true;

        $javeloUserToDeactivate = new User();
        $javeloUserToDeactivate->id = 'javelo-deactivate';
        $javeloUserToDeactivate->active = true;

        $javeloUserToDoNothing = new User();
        $javeloUserToDoNothing->id = 'javelo-nothing';
        $javeloUserToDoNothing->active = false;

        // ===== People side =====

        $userToActivate = $this->createPeopleWithId(1);
        $userToActivate->setDisabled(false); // has ACL → active true → create

        $userToReActivate = $this->createPeopleWithId(2);
        $userToReActivate->setDisabled(false); // has ACL → active true → update (reactivation)

        $userToUpdate = $this->createPeopleWithId(3);
        $userToUpdate->setDisabled(false); // has ACL → active true → update (data change)

        $userToDeactivate = $this->createPeopleWithId(4);
        $userToDeactivate->setDisabled(true); // no ACL → active false → update (deactivation)

        $userToDoNothing = $this->createPeopleWithId(5);
        $userToDoNothing->setDisabled(true); // no ACL + already inactive on Javelo → skip

        return [
            'explicit business scenario' => [
                'javeloUsers' => [$javeloUserToReActivate, $javeloUserToUpdate, $javeloUserToDeactivate, $javeloUserToDoNothing],
                'peopleConcernedId' => [['id' => 1], ['id' => 2], ['id' => 3]], // only these have ACL
                'allPeople' => [$userToActivate, $userToReActivate, $userToUpdate, $userToDeactivate, $userToDoNothing],
                'expectedCreatedEvent' => 1,
                'expectedUpdatedEvent' => 3,
            ],
        ];
    }

    public function createPeopleWithId(int $id): People
    {
        $people = new People();
        $reflection = new \ReflectionClass($people);
        $idProperty = $reflection->getParentClass()->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($people, $id);

        return $people;
    }
}
