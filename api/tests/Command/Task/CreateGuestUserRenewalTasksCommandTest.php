<?php

declare(strict_types=1);

namespace App\Tests\Command\Task;

use App\Command\Task\CreateGuestUserRenewalTasksCommand;
use App\Entity\BaseTask;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use App\Entity\MIS\GuestUser\GuestUser;
use App\Entity\Module\Module;
use App\Entity\Task\RenewGuestUser;
use App\Repository\MIS\GuestUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateGuestUserRenewalTasksCommandTest extends KernelTestCase
{
    use ProphecyTrait;
    /**
     * @var string
     */
    final public const string COMMAND = 'api:guest-user:create-renewal-tasks';

    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testExecute()
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $guestUserRepositoryMock = $this->getMockBuilder(GuestUserRepository::class)->disableOriginalConstructor()->onlyMethods(['findExpiredInOneMonth'])->getMock();

        $guestUser = new GuestUser();
        $reflection = new \ReflectionClass(get_parent_class($guestUser));
        $property = $reflection->getProperty('id');
        $property->setValue($guestUser, 42);
        $guestUser->setBusinessUnit((new BusinessUnit())->setRepresentative(new People()));
        $guestUser->setPlannedDisableAt(new \DateTime());
        $guestUserRepositoryMock->expects($this->once())->method('findExpiredInOneMonth')->willReturn([$guestUser]);

        $moduleRepositoryMock = $this->createMock(EntityRepository::class);
        $entityManagerProphecy->getRepository(Module::class)->shouldBeCalledTimes(1)->willReturn($moduleRepositoryMock);
        $module = new Module();
        $moduleRepositoryMock->expects($this->once())->method('findOneBy')->with(['name' => 'AZGU'])->willReturn($module);

        $renewGuestUserRepositoryMock = $this->createMock(EntityRepository::class);
        $entityManagerProphecy->getRepository(RenewGuestUser::class)->shouldBeCalledTimes(1)->willReturn($renewGuestUserRepositoryMock);
        $renewGuestUserRepositoryMock->expects($this->once())->method('findOneBy')->with([
            'guestUser' => $guestUser,
            'status' => BaseTask::PENDING,
        ])->willReturn(null);

        $entityManagerProphecy->persist(Argument::any())->shouldBeCalledTimes(1);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        $this->application->addCommand(new CreateGuestUserRenewalTasksCommand($guestUserRepositoryMock, $entityManagerProphecy->reveal()));

        $command = $this->application->find(self::COMMAND);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => self::COMMAND,
        ]);
    }
}
