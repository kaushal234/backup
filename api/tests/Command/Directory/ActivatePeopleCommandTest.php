<?php

declare(strict_types=1);

namespace App\Tests\Command\Directory;

use App\Command\Directory\ActivatePeopleCommand;
use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Repository\AclRepository;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ActivatePeopleCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    final public const COMMAND = 'api:people:activate';

    public function testUserIsEnable()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);

        $repositoryMock = $this->createMock(EntityRepository::class);
        $peopleRepository = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findPeopleToActivate', 'enableAndUnhidePeople'])->getMock();
        $aclRepository = $this->getMockBuilder(AclRepository::class)->disableOriginalConstructor()->onlyMethods(['updatePeopleAclByGroup'])->getMock();

        $emProphecy->getRepository(People::class)->shouldBeCalledOnce()->willReturn($peopleRepository);
        $emProphecy->getRepository(Group::class)->shouldBeCalledOnce()->willReturn($repositoryMock);
        $emProphecy->getRepository(Acl::class)->shouldBeCalledOnce()->willReturn($aclRepository);

        $group = (new Group())->setName('ACL_AUTH_INTRANET');
        $people = (new People())->addAcl(new Acl())->setDisabled(true)->setHidden(true)->setFirstname('John')->setLastname('Doe')->setEnableAt(new \DateTime('2026-08-06'));

        $peopleRepository->expects($this->once())->method('findPeopleToActivate')->willReturn([$people]);
        $peopleRepository->expects($this->once())->method('enableAndUnhidePeople')->with($people);

        $repositoryMock->expects($this->once())->method('findOneBy')->with(['name' => 'ACL_AUTH_INTRANET'])->willReturn($group);
        $aclRepository->expects($this->once())->method('updatePeopleAclByGroup')->with($people, $group);
        $emProphecy->flush()->shouldBeCalledOnce();

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new ActivatePeopleCommand($emProphecy->reveal()));

        $command = $application->find(self::COMMAND);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => self::COMMAND,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(\sprintf('Looking for people with an arrival date (enableAt) between %s and %s: 1 found.', (new \DateTime())->format('Y-m-d'), (new \DateTime('+ 1 days'))->format('Y-m-d')), $output);
        $this->assertStringContainsString('Arrival 2026-08-06, activated DOE, John (ACL_AUTH_INTRANET granted).', $output);
        $this->assertStringContainsString('activated 1 people.', $output);
        $this->assertStringContainsString('skipped ACL_AUTH_INTRANET for 0 temporary SFE.', $output);
    }

    public function testSinceOptionIsForwardedToTheRepository()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);

        $repositoryMock = $this->createMock(EntityRepository::class);
        $peopleRepository = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findPeopleToActivate', 'enableAndUnhidePeople'])->getMock();
        $aclRepository = $this->getMockBuilder(AclRepository::class)->disableOriginalConstructor()->onlyMethods(['updatePeopleAclByGroup'])->getMock();

        $emProphecy->getRepository(People::class)->shouldBeCalledOnce()->willReturn($peopleRepository);
        $emProphecy->getRepository(Group::class)->shouldBeCalledOnce()->willReturn($repositoryMock);
        $emProphecy->getRepository(Acl::class)->shouldBeCalledOnce()->willReturn($aclRepository);

        $group = (new Group())->setName('ACL_AUTH_INTRANET');
        $people = (new People())->addAcl(new Acl())->setDisabled(true)->setHidden(true)->setFirstname('John')->setLastname('Doe')->setEnableAt(new \DateTime('2026-08-01'));

        $peopleRepository->expects($this->once())
            ->method('findPeopleToActivate')
            ->with($this->equalTo(new \DateTime('2026-08-01')))
            ->willReturn([$people]);
        $peopleRepository->expects($this->once())->method('enableAndUnhidePeople')->with($people);

        $repositoryMock->expects($this->once())->method('findOneBy')->with(['name' => 'ACL_AUTH_INTRANET'])->willReturn($group);
        $aclRepository->expects($this->once())->method('updatePeopleAclByGroup')->with($people, $group);
        $emProphecy->flush()->shouldBeCalledOnce();

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new ActivatePeopleCommand($emProphecy->reveal()));

        $command = $application->find(self::COMMAND);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => self::COMMAND,
            '--since' => '2026-08-01',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(\sprintf('Looking for people with an arrival date (enableAt) between 2026-08-01 and %s: 1 found.', (new \DateTime('+ 1 days'))->format('Y-m-d')), $output);
    }
}
