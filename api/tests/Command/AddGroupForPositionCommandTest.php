<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\AddGroupForPositionCommand;
use App\Entity\Acl;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Entity\Group;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AddGroupForPositionCommandTest extends KernelTestCase
{
    use ProphecyTrait;
    /**
     * @var string
     */
    final public const COMMAND = 'api:insert:group_for_position';

    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testExecute()
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);

        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findBy'])->getMock();

        $positionRepositoryMock = $this->createMock(EntityRepository::class);

        $position = (new Position())->setDescription('Debout');
        $positionBis = (new Position())->setDescription('Assis');

        $group = (new Group())->setName('ACDC');
        $groupBis = (new Group())->setName('Scorpions');

        $groupRepositoryMock = $this->createMock(EntityRepository::class);

        $entityManagerProphecy->getRepository(Group::class)->shouldBeCalledTimes(1)->willReturn($groupRepositoryMock);
        $entityManagerProphecy->getRepository(Position::class)->shouldBeCalledTimes(1)->willReturn($positionRepositoryMock);

        $groupRepositoryMock->expects($this->once())->method('findAll')->willReturn([$group, $groupBis]);
        $positionRepositoryMock->expects($this->once())->method('findAll')->willReturn([$position, $positionBis]);

        $positionRepositoryMock->expects($this->once())->method('findOneBy')->with(['description' => 'Debout'])->willReturn($position);
        $groupRepositoryMock->expects($this->once())->method('findOneBy')->with(['name' => 'ACDC'])->willReturn($group);

        $peopleWithoutBU = new People();
        $peopleWithBU = (new People())->setBusinessUnit((new BusinessUnit())->setLocation(new Location()));

        $peopleRepositoryMock->expects($this->once())->method('findBy')->with(['position' => $position])->willReturn([$peopleWithBU, $peopleWithoutBU]);

        $acl = (new Acl())
            ->setLocation($peopleWithBU->getBusinessUnit()->getLocation())
            ->setUser($peopleWithBU)
            ->setGroup($group)
        ;

        $entityManagerProphecy->persist($acl)->shouldBeCalledTimes(1);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        $this->application->addCommand(new AddGroupForPositionCommand($entityManagerProphecy->reveal(), $peopleRepositoryMock));

        $command = $this->application->find(self::COMMAND);

        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['ACDC', 'Debout']);
        $commandTester->execute([
            'command' => self::COMMAND,
        ]);
    }
}
