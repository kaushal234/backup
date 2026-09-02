<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\AddGroupForGroupsCommand;
use App\Entity\Acl;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AddGroupForGroupsCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'api:insert:group_for_groups';

    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testExecuteWithAValidGroup()
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findGroupsMembers'])->getMock();
        $groupRepositoryMock = $this->createMock(EntityRepository::class);

        $group = (new Group())->setName('NEW_GROUP');
        $groupRepositoryMock->expects($this->once())->method('findOneBy')->with(['name' => 'NEW_GROUP'])->willReturn($group);

        $aclLocation = new Location();

        $entityManagerProphecy->persist(Argument::that(static fn (Acl $object) => $object->getLocation() === $aclLocation && 'NEW_GROUP' === $object->getGroup()->getName()))->shouldBeCalledTimes(150);

        $entityManagerProphecy->flush()->shouldBeCalledTimes(2);

        $entityManagerProphecy->getRepository(People::class)->shouldBeCalledTimes(1)->willReturn($peopleRepositoryMock);
        $entityManagerProphecy->getRepository(Group::class)->shouldBeCalledTimes(1)->willReturn($groupRepositoryMock);

        $peopleRepositoryMock->expects($this->once())->method('findGroupsMembers')->with(['GRP1', 'GRP2', 'GRP3'])->willReturn($this->providePeople(150, $aclLocation));
        $this->application->addCommand(new AddGroupForGroupsCommand($entityManagerProphecy->reveal()));

        $command = $this->application->find(self::COMMAND);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => self::COMMAND,
            'group' => 'NEW_GROUP',
            'groups' => ['GRP1', 'GRP2', 'GRP3'],
        ]);
    }

    public function testExecuteWithANonExistingGroup()
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $peopleRepositoryMock = $this->createMock(PeopleRepository::class);
        $groupRepositoryMock = $this->createMock(EntityRepository::class);

        $groupRepositoryMock->expects($this->once())->method('findOneBy')->with(['name' => 'NEW_GROUP'])->willReturn(null);

        $entityManagerProphecy->persist(Argument::any())->shouldNotBeCalled();
        $entityManagerProphecy->flush()->shouldNotBeCalled();

        $peopleRepositoryMock->expects($this->never())->method('findGroupsMembers');

        $entityManagerProphecy->getRepository(People::class)->shouldBeCalledTimes(1)->willReturn($peopleRepositoryMock);
        $entityManagerProphecy->getRepository(Group::class)->shouldBeCalledTimes(1)->willReturn($groupRepositoryMock);

        $this->application->addCommand(new AddGroupForGroupsCommand($entityManagerProphecy->reveal()));

        $command = $this->application->find(self::COMMAND);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => self::COMMAND,
            'group' => 'NEW_GROUP',
            'groups' => ['GRP1', 'GRP2', 'GRP3'],
        ]);
    }

    private function providePeople(int $int, Location $aclLocation)
    {
        $users = [];
        for ($i = 0; $i < $int; ++$i) {
            $people = (new People())
                ->setBusinessUnit((new BusinessUnit())->setLocation($aclLocation))
                ->setUsername(\sprintf('people_%3d', $int))
            ;

            $users[] = $people;
        }

        return $users;
    }
}
