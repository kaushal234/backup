<?php

declare(strict_types=1);

namespace App\Tests\EventListener\MinutesOfMeeting;

use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Action;
use App\Entity\MinutesOfMeeting\Meeting;
use App\EventListener\MinutesOfMeeting\MeetingWriteListener;
use App\Notifier\MinutesOfMeeting\MinutesOfMeetingNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class MeetingWriteListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testQuickMeetingMeetingIsClosedWhenCreated()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);

        $listener = new MeetingWriteListener($serviceLocatorProphecy->reveal());

        $meeting = new Meeting();
        $people = new People();
        $meeting->setQuick(true);

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $listener->onMeetingPreWrite(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $meeting));

        self::assertSame(Meeting::CLOSED, $meeting->getStatus());
    }

    public function testNormalMeetingIsNotClosedWhenCreated()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);

        $listener = new MeetingWriteListener($serviceLocatorProphecy->reveal());

        $meeting = new Meeting();
        $people = new People();

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $listener->onMeetingPreWrite(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $meeting));

        self::assertSame(Meeting::OPEN, $meeting->getStatus());
    }

    public function testCreatorOfTheMeetingIsAddedInAtteendeesIfIsNotIn()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);

        $people = new People();
        $meeting = new Meeting();

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $listener = new MeetingWriteListener($serviceLocatorProphecy->reveal());
        $listener->onMeetingPreWrite(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $meeting));

        self::assertTrue($meeting->getAttendees()->contains($people));
    }

    public function testEmailAreSentWhenMeetingIsCreated()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $notifier = $this->prophesize(MinutesOfMeetingNotifier::class);
        $serviceLocatorProphecy->get(MinutesOfMeetingNotifier::class)->shouldBeCalledTimes(1)->willReturn($notifier->reveal());

        $listener = new MeetingWriteListener($serviceLocatorProphecy->reveal());

        $meeting = new Meeting();
        $notifier->sendCreationEmail($meeting)->shouldBeCalledTimes(1);

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $listener->onMeetingPostWrite(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $meeting));
    }

    public function testEmailAreSentWhenMeetingStatusIsUpdated()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $notifier = $this->prophesize(MinutesOfMeetingNotifier::class);
        $serviceLocatorProphecy->get(MinutesOfMeetingNotifier::class)->shouldBeCalledTimes(1)->willReturn($notifier->reveal());
        $listener = new MeetingWriteListener($serviceLocatorProphecy->reveal());

        $meeting = new Meeting();
        $meeting->setStatus(Meeting::RELEASED);

        $previousMeeting = new Meeting();
        $previousMeeting->setStatus(Meeting::OPEN);

        $notifier->sendStatusEmail($meeting)->shouldBeCalledTimes(1);

        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);
        $request->attributes->set('previous_data', $previousMeeting);
        $request->attributes->set('_route', 'update_meeting_status');

        $listener->onMeetingPostWrite(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $meeting));
    }

    public function testTasksLegacyAreClosedWhenMomIsDeleted()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $taskManagerProphecy = $this->prophesize(TaskManager::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $actionRepositoryMock = $this->createMock(EntityRepository::class);

        $request = new Request();
        $request->setMethod(Request::METHOD_DELETE);

        $meeting = new Meeting();
        $meeting->addAction((new Action())->setTask(12));

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $serviceLocatorProphecy->get(TaskManager::class)->shouldBeCalledTimes(1)->willReturn($taskManagerProphecy->reveal());
        $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldBeCalledTimes(1)->willReturn($entityManagerProphecy->reveal());
        $entityManagerProphecy->getRepository(Action::class)->shouldBeCalledTimes(1)->willReturn($actionRepositoryMock);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people = new People());
        $actionRepositoryMock->expects($this->once())->method('count')->with(['task' => 12])->willReturn(1);
        $taskManagerProphecy->close(Argument::type(Task::class), 'This MOM has been deleted', $people)->shouldBeCalledTimes(1);

        $listener = new MeetingWriteListener($serviceLocatorProphecy->reveal());
        $listener->onMeetingDelete(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $meeting));
    }

    public function testTasksLegacyAreNotClosedWhenDuplicatedMomIsDeleted()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $taskManagerProphecy = $this->prophesize(TaskManager::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $actionRepositoryMock = $this->createMock(EntityRepository::class);

        $request = new Request();
        $request->setMethod(Request::METHOD_DELETE);

        $meeting = new Meeting();
        $meeting->addAction((new Action())->setTask(12));

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $serviceLocatorProphecy->get(TaskManager::class)->shouldBeCalledTimes(1)->willReturn($taskManagerProphecy->reveal());
        $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldBeCalledTimes(1)->willReturn($entityManagerProphecy->reveal());
        $entityManagerProphecy->getRepository(Action::class)->shouldBeCalledTimes(1)->willReturn($actionRepositoryMock);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people = new People());
        $actionRepositoryMock->expects($this->once())->method('count')->with(['task' => 12])->willReturn(2);
        $taskManagerProphecy->close(Argument::type(Task::class), 'This MOM has been deleted', $people)->shouldNotBeCalled();

        $listener = new MeetingWriteListener($serviceLocatorProphecy->reveal());
        $listener->onMeetingDelete(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $meeting));
    }
}
