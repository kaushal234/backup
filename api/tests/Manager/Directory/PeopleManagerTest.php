<?php

declare(strict_types=1);

namespace App\Tests\Manager\Directory;

use App\Entity\Acl;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Manager\Directory\PeopleManager;
use App\Notifier\Tasks\LegacyTaskNotifier;
use App\Repository\Directory\PeopleRepository;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class PeopleManagerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider providePeople
     */
    public function testHasGroup(People $people, string $groupName, ?Location $location, bool $expected)
    {
        self::assertSame($expected, PeopleManager::hasGroup($people, $groupName, $location));
    }

    /**
     * @dataProvider providePeopleAndGroups
     */
    public function testHasOneOfGroup(People $people, array $groupName, ?Location $location, bool $expected)
    {
        self::assertSame($expected, PeopleManager::hasOneOfGroups($people, $groupName, $location));
    }

    public function providePeople()
    {
        yield 'people does not have the group' => [new People(), 'UNKNOWN', null, false];

        $acl = (new Acl())->setGroup((new Group())->setName('USCULE'));
        $people = (new People())->addAcl($acl);

        yield 'people has the group' => [$people, 'USCULE', null, true];

        $acl = (new Acl())->setGroup((new Group())->setName('USCULE'))->setLocation(new Location());
        $people = (new People())->addAcl($acl);

        yield 'people has the group but not on the right location' => [$people, 'USCULE', new Location(), false];

        $location = new Location();
        $acl = (new Acl())->setGroup((new Group())->setName('USCULE'))->setLocation($location);
        $people = (new People())->addAcl($acl);

        yield 'people has the group on the right location' => [$people, 'USCULE', $location, true];
    }

    public function providePeopleAndGroups()
    {
        yield 'people does not have one of the groups' => [new People(), ['USCULE', 'UNKNOWN'], null, false];

        $acl = (new Acl())->setGroup((new Group())->setName('IES'));
        $people = (new People())->addAcl($acl);

        yield 'people has one of the groups' => [$people, ['USCULE', 'IES'], null, true];

        $acl = (new Acl())->setGroup((new Group())->setName('USCULE'))->setLocation(new Location());
        $people = (new People())->addAcl($acl);

        yield 'people has one of the groups but not on the right location' => [$people, ['USCULE', 'IES'], new Location(), false];

        $location = new Location();
        $acl = (new Acl())->setGroup((new Group())->setName('USCULE'))->setLocation($location);
        $people = (new People())->addAcl($acl);

        yield 'people has one of the groups on the right location' => [$people, ['IES', 'USCULE'], $location, true];
    }

    public function testGenerateUsernameAndEmailWithDomain()
    {
        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findBy'])->getMock();

        $user = new People();
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $businessUnit = new BusinessUnit();
        $businessUnit->setName('Business Unit');
        $businessUnit->setDomain('@tld-tld.com');
        $user->setBusinessUnit($businessUnit);

        $peopleRepositoryMock->expects($this->once())->method('findBy')->with(['email' => 'john.doe@tld-tld.com'])->willReturn([]);

        $service = new PeopleManager(
            $peopleRepositoryMock,
            $this->prophesize(TaskManager::class)->reveal(),
            $this->prophesize(LegacyTaskNotifier::class)->reveal(),
        );

        $service->generateUsernameAndEmail($user);

        $this->assertSame('john.doe@tld-tld.com', $user->getEmail());
        $this->assertSame('john.doe@tld-tld.com', $user->getUsername());
    }

    public function testGenerateUsernameAndEmailWithNoBu()
    {
        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findBy'])->getMock();

        $user = new People();
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $businessUnit = new BusinessUnit();
        $businessUnit->setName('Business Unit');
        $businessUnit->setDomain('@tld-gse.com');
        $user->setBusinessUnit($businessUnit);

        $peopleRepositoryMock->expects($this->exactly(3))->method('findBy')->withConsecutive(
            [['email' => 'john.doe@tld-gse.com']],
            [['email' => 'john.doe1@tld-gse.com']],
            [['email' => 'john.doe2@tld-gse.com']],
        )->willReturnOnConsecutiveCalls(
            [new People()],
            [new People()],
            [],
        );

        $service = new PeopleManager(
            $peopleRepositoryMock,
            $this->prophesize(TaskManager::class)->reveal(),
            $this->prophesize(LegacyTaskNotifier::class)->reveal(),
        );

        $service->generateUsernameAndEmail($user);

        $this->assertSame('john.doe2@tld-gse.com', $user->getEmail());
        $this->assertSame('john.doe2@tld-gse.com', $user->getUsername());
    }

    public function testGenerateUsernameAndEmailWhenDuplicateExists()
    {
        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findBy'])->getMock();

        $user = new People();
        $user->setFirstname('John');
        $user->setLastname('Doe');

        $peopleRepositoryMock->expects($this->once())->method('findBy')->with(['email' => 'john.doe@tld-gse.com'])->willReturn([]);

        $service = new PeopleManager(
            $peopleRepositoryMock,
            $this->prophesize(TaskManager::class)->reveal(),
            $this->prophesize(LegacyTaskNotifier::class)->reveal(),
        );

        $service->generateUsernameAndEmail($user);

        $this->assertSame('john.doe@tld-gse.com', $user->getEmail());
        $this->assertSame('john.doe@tld-gse.com', $user->getUsername());
    }

    public function testGenerateUsernameAndEmailThrowsExceptionAfter50Attempts()
    {
        $user = new People();
        $user->setFirstname('John');
        $user->setLastname('Doe');

        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findBy'])->getMock();
        $peopleRepositoryMock->expects($this->atMost(51))->method('findBy')->with($this->callback(static fn () => true))->willReturn([new People()]);

        $service = new PeopleManager(
            $peopleRepositoryMock,
            $this->prophesize(TaskManager::class)->reveal(),
            $this->prophesize(LegacyTaskNotifier::class)->reveal(),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to generate a unique email address after 50 attempts.');

        $service->generateUsernameAndEmail($user);
    }

    public function testCreateTaskAndNotifyAssigneeWhenNewMisPeople()
    {
        $businessUnit = new BusinessUnit();
        $businessUnit->setLocation(new Location());

        $people = new People();
        $people->setLegacyId(1);
        $people->setBusinessUnit($businessUnit);

        $taskManagerProphecy = $this->prophesize(TaskManager::class);
        $taskManagerProphecy->insert(Argument::type(Task::class))->shouldBeCalledOnce();

        $notifierProphecy = $this->prophesize(LegacyTaskNotifier::class);
        $notifierProphecy->sendEmail(Argument::type(Task::class))->shouldBeCalledOnce();

        $peopleManager = new PeopleManager(
            $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->getMock(),
            $taskManagerProphecy->reveal(),
            $notifierProphecy->reveal(),
        );

        $peopleManager->createTaskForMISUserAndNotify($people);
    }

    public function testNoCreateTaskAndNotifyAssigneeWhenNewMisPeople()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Task not created. Reason : test');
        $businessUnit = new BusinessUnit();
        $businessUnit->setLocation(new Location());

        $people = new People();
        $people->setLegacyId(1);
        $people->setBusinessUnit($businessUnit);

        $taskManagerProphecy = $this->prophesize(TaskManager::class);
        $taskManagerProphecy->insert(Argument::type(Task::class))->shouldBeCalledOnce()->willThrow(new \Exception('test'));

        $notifierProphecy = $this->prophesize(LegacyTaskNotifier::class);
        $notifierProphecy->sendEmail(Argument::type(Task::class))->shouldNotBeCalled();

        $peopleManager = new PeopleManager(
            $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->getMock(),
            $taskManagerProphecy->reveal(),
            $notifierProphecy->reveal(),
        );

        $peopleManager->createTaskForMISUserAndNotify($people);
    }
}
