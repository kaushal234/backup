<?php

declare(strict_types=1);

namespace App\Tests\Manager\Transfer;

use App\Doctrine\OwnerReflectionBag;
use App\Entity\Directory\People;
use App\Manager\Transfer\Handler\SupervisorTransferHandler;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class SupervisorTransferHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testHandler(): void
    {
        $target = new People();
        $teamMembers = [new People(), new People(), $target];
        $supervisor = new People();
        $supervisorLevel1 = new People();
        $relation = new OwnerReflectionBag(new \ReflectionClass(People::class), new \ReflectionProperty(People::class, 'supervisor'));
        $supervisor->setSupervisor($supervisorLevel1);
        foreach ($teamMembers as $teamMember) {
            $teamMember->setSupervisor($supervisor);
        }

        $peopleRepository = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['getSubordinates'])->getMock();
        $peopleRepository->expects($this->once())->method('getSubordinates')->with($supervisor, 1)->willReturn($teamMembers);

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(People::class)->shouldBeCalledOnce()->willReturn($peopleRepository);
        $entityManager->persist(Argument::type(People::class))->shouldBeCalledTimes(3);
        $entityManager->flush()->shouldBeCalledOnce();

        $handler = new SupervisorTransferHandler();
        $handler->setEntityManager($entityManager->reveal());
        $handler->handle($supervisor, $target, $relation);

        foreach ($teamMembers as $teamMember) {
            self::assertTrue($teamMember->getSupervisor() === $supervisorLevel1);
        }
    }

    public function testHandleDoesNothingIfNotSupported(): void
    {
        $supervisor = new People();
        $target = new People();

        $relation = new OwnerReflectionBag(new \ReflectionClass(People::class), new \ReflectionProperty(People::class, 'mentor'));

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(People::class)->shouldNotBeCalled();

        $handler = new SupervisorTransferHandler();
        $handler->setEntityManager($entityManager->reveal());
        $handler->handle($supervisor, $target, $relation);

        $entityManager->persist(Argument::any())->shouldNotHaveBeenCalled();
    }
}
