<?php

declare(strict_types=1);

namespace App\Tests\Command\HumanResources;

use App\Command\HumanResources\AnnualEmployeeRevisionCommand;
use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Notifier\Tasks\SequenceNotifier;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Factory\Sequence\HumanResources\AnnualRevisionSequenceFactory;
use LegacyBundle\Manager\SequenceManager;
use LegacyBundle\Model\Sequence;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AnnualEmployeeRevisionCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    public const COMMAND = 'api:human-resources:annual-revision';

    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testExecuteWithBasicSequence(): void
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $sequenceManagerProphecy = $this->prophesize(SequenceManager::class);
        $sequenceNotifierProphecy = $this->prophesize(SequenceNotifier::class);
        $factoryProphecy = $this->prophesize(AnnualRevisionSequenceFactory::class);
        $peopleRepository = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findBy'])->getMock();

        $entityManagerProphecy->getRepository(People::class)->shouldBeCalledOnce()->willReturn($peopleRepository);

        $acl = (new Acl())->setGroup((new Group())->setName('ACL_AUTH_INTRANET'));
        $people = (new People())->addAcl($acl)->setCreatedAt((new \DateTime('today'))->modify('- 4 years'));
        $peopleRepository->expects($this->once())->method('findBy')->with(['disabled' => false, 'hidden' => false])->willReturn([$people]);

        $factoryProphecy->createAnnualRevisionSequence($people, 'user.annual_revision', $people)->shouldBeCalledOnce()->willReturn($sequence = new Sequence());
        $sequenceManagerProphecy->insert($sequence)->shouldBeCalledOnce();
        $sequenceNotifierProphecy->sendEmail($sequence)->shouldBeCalledOnce();

        $this->application->addCommand(new AnnualEmployeeRevisionCommand($sequenceManagerProphecy->reveal(), $entityManagerProphecy->reveal(), $sequenceNotifierProphecy->reveal(), $factoryProphecy->reveal()));
        $command = $this->application->find(self::COMMAND);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => self::COMMAND]);
    }

    public function testExecuteWithLightSequence(): void
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $sequenceManagerProphecy = $this->prophesize(SequenceManager::class);
        $sequenceNotifierProphecy = $this->prophesize(SequenceNotifier::class);
        $factoryProphecy = $this->prophesize(AnnualRevisionSequenceFactory::class);
        $peopleRepository = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findBy'])->getMock();

        $entityManagerProphecy->getRepository(People::class)->shouldBeCalledOnce()->willReturn($peopleRepository);

        $supervisor = new People();
        $people = (new People())
            ->setSupervisor($supervisor)
            ->setCreatedAt((new \DateTime('today'))->modify('- 4 years'))
        ;
        $peopleRepository->expects($this->once())->method('findBy')->with(['disabled' => false, 'hidden' => false])->willReturn([$people]);

        $factoryProphecy->createAnnualRevisionSequence($people, 'user.annual_revision.light', $supervisor)->shouldBeCalledOnce()->willReturn($sequence = new Sequence());
        $sequenceManagerProphecy->insert($sequence)->shouldBeCalledOnce();
        $sequenceNotifierProphecy->sendEmail($sequence)->shouldBeCalledOnce();

        $this->application->addCommand(new AnnualEmployeeRevisionCommand($sequenceManagerProphecy->reveal(), $entityManagerProphecy->reveal(), $sequenceNotifierProphecy->reveal(), $factoryProphecy->reveal()));
        $command = $this->application->find(self::COMMAND);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => self::COMMAND]);
    }
}
