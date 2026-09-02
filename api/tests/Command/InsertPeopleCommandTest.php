<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\InsertPeopleCommand;
use App\Entity\Acl;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Department;
use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Entity\Group;
use App\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class InsertPeopleCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'tld:insert:people';

    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testPeopleIsInserted()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $userManagerProphecy = $this->prophesize(UserManager::class);

        $peopleRepositoryMock = $this->createMock(EntityRepository::class);
        $businessUnitRepositoryMock = $this->createMock(EntityRepository::class);
        $positionRepositoryMock = $this->createMock(EntityRepository::class);
        $departmentRepositoryMock = $this->createMock(EntityRepository::class);
        $groupRepositoryMock = $this->createMock(EntityRepository::class);

        $emProphecy->getRepository(People::class)->shouldBeCalledTimes(1)->willReturn($peopleRepositoryMock);
        $emProphecy->getRepository(BusinessUnit::class)->shouldBeCalledTimes(1)->willReturn($businessUnitRepositoryMock);
        $emProphecy->getRepository(Position::class)->shouldBeCalledTimes(1)->willReturn($positionRepositoryMock);
        $emProphecy->getRepository(Department::class)->shouldBeCalledTimes(1)->willReturn($departmentRepositoryMock);
        $emProphecy->getRepository(Group::class)->shouldBeCalledTimes(1)->willReturn($groupRepositoryMock);

        $peopleRepositoryMock->expects($this->once())->method('find')->with(5)->willReturn($supervisor = new People());
        $businessUnitRepositoryMock->expects($this->once())->method('find')->with(1)->willReturn($businessUnit = new BusinessUnit());
        $positionRepositoryMock->expects($this->once())->method('find')->with(2)->willReturn($position = new Position());
        $departmentRepositoryMock->expects($this->once())->method('find')->with(4)->willReturn($department = new Department());
        $groupRepositoryMock->expects($this->once())->method('findOneBy')->with(['name' => 'ACL_AUTH_INTRANET'])->willReturn($group = new Group());

        $people = (new People())
            ->setFirstname('Gilles')
        ;

        $userManagerProphecy->generatePassword(Argument::that(static fn (People $peopleToCompare) => $people->getFirstname() === $peopleToCompare->getFirstname()))->shouldBeCalledTimes(1);

        $emProphecy->persist(Argument::that(static function ($data) {
            switch (true) {
                case $data instanceof People:
                    return !$data->isHidden() && !$data->isDisabled();
                case $data instanceof Acl:
                    return true;
                default:
                    return false;
            }
        }))->shouldBeCalledTimes(2);

        $emProphecy->flush()->shouldBeCalledTimes(2);

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new InsertPeopleCommand($emProphecy->reveal(), $userManagerProphecy->reveal()));

        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
            'lastname' => 'Ayjaune',
            'firstname' => 'Gilles',
            'jobTitle' => 'Casseur/Chômeur',
            'email' => 'gille.ayjaune@facebook.fr',
            'businessUnit' => '1',
            'position' => '2',
            'department' => '4',
            'supervisor' => '5',
            'language' => 'en',
        ], [
            'phone' => '+33 6 01 02 03 04',
            'mobile' => '+33 6 01 02 03 04',
        ]);
    }
}
