<?php

declare(strict_types=1);

namespace App\Tests\Command\Sales\CustomerRelationshipTeam;

use App\Command\Sales\CustomerRelationshipTeam\CustomerRelationshipTeamUpdateCommand;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Directory\SubDivision;
use App\Entity\Sales\CustomerRelationshipTeam;
use App\Repository\Directory\LocationRepository;
use App\Repository\Directory\PeopleRepository;
use App\Repository\Sales\CustomerRelationshipTeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CustomerRelationshipTeamUpdateCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'api:crt:update';

    public function testCRTsAreUpdated()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $crtRepositoryMock = $this->getMockBuilder(CustomerRelationshipTeamRepository::class)->disableOriginalConstructor()->onlyMethods(['findCustomerRelationshipTeamsToUpdate'])->getMock();
        $subDivisionRepositoryMock = $this->createMock(EntityRepository::class);
        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['find'])->getMock();
        $locationRepositoryMock = $this->getMockBuilder(LocationRepository::class)->disableOriginalConstructor()->onlyMethods(['find'])->getMock();

        $emProphecy->getRepository(People::class)->shouldBeCalledTimes(4)->willReturn($peopleRepositoryMock);
        $emProphecy->getRepository(Location::class)->shouldBeCalledTimes(4)->willReturn($locationRepositoryMock);
        $emProphecy->getRepository(SubDivision::class)->shouldBeCalledTimes(1)->willReturn($subDivisionRepositoryMock);
        $emProphecy->getRepository(CustomerRelationshipTeam::class)->shouldBeCalledTimes(1)->willReturn($crtRepositoryMock);

        $subDivisionRepositoryMock->expects($this->once())->method('find')->with(11)->willReturn($subDivision = new SubDivision());

        $peopleRepositoryMock->expects($this->exactly(4))->method('find')->withConsecutive([10], [13], [14], [15])->willReturnOnConsecutiveCalls($salesRep = new People(), $targetSalesRep = new People(), $targetPartsRep = new People(), $targetServiceRep = new People());

        $locationRepositoryMock->expects($this->exactly(4))->method('find')->withConsecutive([12], [16], [17], [18])->willReturnOnConsecutiveCalls($location = new Location(), $targetERPLocation = new Location(), $targetServiceLocation = new Location(), $targetPartsLocation = new Location());

        $crt = (new CustomerRelationshipTeam())
            ->setSalesRepresentative($salesRep)
            ->setPartsRepresentative((new People())->setLegacyId(99))
            ->setServiceRepresentative((new People())->setLegacyId(99))
            ->setErpLocation($location)
            ->setServiceLocation((new Location())->setLegacyId(99))
            ->setPartsLocation((new Location())->setLegacyId(99))
        ;

        $crtRepositoryMock->expects($this->once())->method('findCustomerRelationshipTeamsToUpdate')->with(['salesRepresentative' => $salesRep, 'erpLocation' => $location, 'subDivision' => $subDivision])->willReturn([$crt]);

        $emProphecy->persist(Argument::that(static fn ($crt) => $crt instanceof CustomerRelationshipTeam
            && $crt->getSalesRepresentative() === $targetSalesRep
            && $crt->getPartsRepresentative() === $targetPartsRep
            && $crt->getServiceRepresentative() === $targetServiceRep
            && $crt->getErpLocation() === $targetERPLocation
            && $crt->getPartsLocation() === $targetPartsLocation
            && $crt->getServiceLocation() === $targetServiceLocation
        ))->shouldBeCalledTimes(1);

        $emProphecy->flush()->shouldBeCalledTimes(1);

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new CustomerRelationshipTeamUpdateCommand($emProphecy->reveal()));

        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute(
            [
                'command' => self::COMMAND,
                '--salesRepresentative' => 10,
                '--subDivision' => 11,
                '--erpLocation' => 12,
                '--targetSalesRepresentative' => 13,
                '--targetPartsRepresentative' => 14,
                '--targetServiceRepresentative' => 15,
                '--targetERPLocation' => 16,
                '--targetServiceLocation' => 17,
                '--targetPartsLocation' => 18,
            ],
        );
    }

    public function testReturnNothingWhenNoOptionPassed()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $crtRepositoryMock = $this->getMockBuilder(CustomerRelationshipTeamRepository::class)->disableOriginalConstructor()->onlyMethods(['findCustomerRelationshipTeamsToUpdate'])->getMock();
        $subDivisionRepositoryMock = $this->createMock(EntityRepository::class);
        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['find'])->getMock();
        $locationRepositoryMock = $this->getMockBuilder(LocationRepository::class)->disableOriginalConstructor()->onlyMethods(['find'])->getMock();

        $emProphecy->getRepository(People::class)->shouldNotBeCalled();
        $emProphecy->getRepository(Location::class)->shouldNotBeCalled();
        $emProphecy->getRepository(SubDivision::class)->shouldNotBeCalled();
        $emProphecy->getRepository(CustomerRelationshipTeam::class)->shouldNotBeCalled();

        $peopleRepositoryMock->expects($this->never())->method('find');
        $subDivisionRepositoryMock->expects($this->never())->method('find');
        $locationRepositoryMock->expects($this->never())->method('find');

        $crtRepositoryMock->expects($this->never())->method('findCustomerRelationshipTeamsToUpdate');
        $emProphecy->persist()->shouldNotBeCalled();
        $emProphecy->flush()->shouldNotBeCalled();

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new CustomerRelationshipTeamUpdateCommand($emProphecy->reveal()));

        $command = $application->find(self::COMMAND);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => self::COMMAND]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Nothing could be found with filter options given, or no filters given', $output);
    }

    public function testReturnNothingWhenNoTargetPassed()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $crtRepositoryMock = $this->getMockBuilder(CustomerRelationshipTeamRepository::class)->disableOriginalConstructor()->onlyMethods(['findCustomerRelationshipTeamsToUpdate'])->getMock();
        $subDivisionRepositoryMock = $this->createMock(EntityRepository::class);
        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['find'])->getMock();
        $locationRepositoryMock = $this->getMockBuilder(LocationRepository::class)->disableOriginalConstructor()->onlyMethods(['find'])->getMock();

        $emProphecy->getRepository(People::class)->shouldBeCalledTimes(1)->willReturn($peopleRepositoryMock);
        $emProphecy->getRepository(Location::class)->shouldNotBeCalled();
        $emProphecy->getRepository(SubDivision::class)->shouldNotBeCalled();
        $emProphecy->getRepository(CustomerRelationshipTeam::class)->shouldNotBeCalled();

        $peopleRepositoryMock->expects($this->once())->method('find')->with(10)->willReturn($salesRep = new People());
        $subDivisionRepositoryMock->expects($this->never())->method('find');
        $locationRepositoryMock->expects($this->never())->method('find');

        $crtRepositoryMock->expects($this->never())->method('findCustomerRelationshipTeamsToUpdate');
        $emProphecy->persist()->shouldNotBeCalled();
        $emProphecy->flush()->shouldNotBeCalled();

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new CustomerRelationshipTeamUpdateCommand($emProphecy->reveal()));

        $command = $application->find(self::COMMAND);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => self::COMMAND, '--salesRepresentative' => 10]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Nothing could be found with target options given, or no targets given', $output);
    }
}
