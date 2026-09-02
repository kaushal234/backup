<?php

declare(strict_types=1);

namespace App\Tests\EventListener\MIS;

use App\Entity\BaseTask;
use App\Entity\Common\Notification\Notification;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\MIS\TroubleTicket\Type;
use App\Entity\Module\Module;
use App\EventListener\MIS\TroubleTicketListener;
use App\Factory\Common\Notification\MIS\TroubleTicketAssignedNotificationFactory;
use App\Manager\MIS\TroubleTicket\TroubleTicketManager;
use App\Notifier\MIS\TroubleTicket\RecipientsFinder;
use App\Workflow\WorkflowStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class TroubleTicketListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    // --------------------------------------------------------------------
    // onPreCreate
    // --------------------------------------------------------------------

    public function testCreateRequestByRegularUserGoesPendingMoo(): void
    {
        $moo = new People();
        $gku = new People();
        $creator = new People();

        $module = $this->buildModule($moo, $gku, true);
        $defaultAssignee = new People();

        $troubleTicket = $this->buildTroubleTicket(Type::REQUEST, $module, $creator);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $troubleTicketManagerProphecy = $this->prophesize(TroubleTicketManager::class);
        $troubleTicketManagerProphecy->getDefaultAssignee($troubleTicket)->shouldBeCalledTimes(1)->willReturn($defaultAssignee);
        $serviceLocatorProphecy->get(TroubleTicketManager::class)->shouldBeCalledTimes(1)->willReturn($troubleTicketManagerProphecy->reveal());
        $serviceLocatorProphecy->get(Security::class)->shouldNotBeCalled();

        $listener = new TroubleTicketListener($serviceLocatorProphecy->reveal());
        $listener->onPreCreate($this->buildCreateEvent($troubleTicket));

        self::assertSame(TroubleTicket::PENDING_MOO, $troubleTicket->getStatus());
        self::assertSame($defaultAssignee, $troubleTicket->assignee);
    }

    public function testCreateRequestByModuleOperationalOwnerGoesStraightToPending(): void
    {
        $moo = new People();
        $gku = new People();

        $module = $this->buildModule($moo, $gku, true);

        // The creator IS the MOO of the module.
        $troubleTicket = $this->buildTroubleTicket(Type::REQUEST, $module, $moo);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(TroubleTicketManager::class)->shouldNotBeCalled();
        $serviceLocatorProphecy->get(Security::class)->shouldNotBeCalled();

        $listener = new TroubleTicketListener($serviceLocatorProphecy->reveal());
        $listener->onPreCreate($this->buildCreateEvent($troubleTicket));

        self::assertSame(BaseTask::PENDING, $troubleTicket->getStatus());
        self::assertNull($troubleTicket->assignee);
    }

    public function testCreateRequestByModuleKeyUserGoesStraightToPending(): void
    {
        $moo = new People();
        $gku = new People();

        $module = $this->buildModule($moo, $gku, true);

        // The creator IS the GKU (key user) of the module.
        $troubleTicket = $this->buildTroubleTicket(Type::REQUEST, $module, $gku);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(TroubleTicketManager::class)->shouldNotBeCalled();
        $serviceLocatorProphecy->get(Security::class)->shouldNotBeCalled();

        $listener = new TroubleTicketListener($serviceLocatorProphecy->reveal());
        $listener->onPreCreate($this->buildCreateEvent($troubleTicket));

        self::assertSame(BaseTask::PENDING, $troubleTicket->getStatus());
        self::assertNull($troubleTicket->assignee);
    }

    public function testCreateIncidentOnMisRelativeModuleByMooStillSkipsAssignment(): void
    {
        $moo = new People();
        $gku = new People();

        // isMisRelative = true already bypasses PENDING_MOO for a regular creator;
        // this asserts the MOO-creator branch does not accidentally re-assign it to the MOO.
        $module = $this->buildModule($moo, $gku, true);

        $troubleTicket = $this->buildTroubleTicket(Type::INCIDENT, $module, $moo);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(TroubleTicketManager::class)->shouldNotBeCalled();
        $serviceLocatorProphecy->get(Security::class)->shouldNotBeCalled();

        $listener = new TroubleTicketListener($serviceLocatorProphecy->reveal());
        $listener->onPreCreate($this->buildCreateEvent($troubleTicket));

        self::assertSame(BaseTask::PENDING, $troubleTicket->getStatus());
        self::assertNull($troubleTicket->assignee);
    }

    public function testCreateIncidentOnNonMisRelativeModuleByGkuSkipsPendingMoo(): void
    {
        $moo = new People();
        $gku = new People();

        // isMisRelative = false would normally force PENDING_MOO for an INCIDENT; the GKU-creator
        // branch must still take priority and skip MOO/GKU validation.
        $module = $this->buildModule($moo, $gku, false);

        $troubleTicket = $this->buildTroubleTicket(Type::INCIDENT, $module, $gku);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(TroubleTicketManager::class)->shouldNotBeCalled();
        $serviceLocatorProphecy->get(Security::class)->shouldNotBeCalled();

        $listener = new TroubleTicketListener($serviceLocatorProphecy->reveal());
        $listener->onPreCreate($this->buildCreateEvent($troubleTicket));

        self::assertSame(BaseTask::PENDING, $troubleTicket->getStatus());
        self::assertNull($troubleTicket->assignee);
    }

    public function testCreateResolvesCreatedByFromSecurityWhenMissing(): void
    {
        $moo = new People();
        $gku = new People();
        $module = $this->buildModule($moo, $gku, true);

        // The current user IS the MOO but the ticket doesn't have createdBy pre-filled yet:
        // the listener must resolve it from Security before doing the MOO/GKU check.
        $troubleTicket = $this->buildTroubleTicket(Type::REQUEST, $module, null);

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($moo);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $serviceLocatorProphecy->get(TroubleTicketManager::class)->shouldNotBeCalled();

        $listener = new TroubleTicketListener($serviceLocatorProphecy->reveal());
        $listener->onPreCreate($this->buildCreateEvent($troubleTicket));

        self::assertSame($moo, $troubleTicket->createdBy);
        self::assertSame(BaseTask::PENDING, $troubleTicket->getStatus());
        self::assertNull($troubleTicket->assignee);
    }

    // --------------------------------------------------------------------
    // onPostUpdate
    // --------------------------------------------------------------------

    public function testUpdateTypeChangeByRegularUserGoesPendingMoo(): void
    {
        $moo = new People();
        $gku = new People();
        $creator = new People();
        $module = $this->buildModule($moo, $gku, true);
        $defaultAssignee = new People();

        $previousType = $this->buildType(Type::INCIDENT);
        $newType = $this->buildType(Type::REQUEST);

        $previousData = $this->buildTroubleTicket(null, $module, $creator);
        $previousData->type = $previousType;

        $troubleTicket = $this->buildTroubleTicket(null, $module, $creator);
        $troubleTicket->type = $newType;
        $troubleTicket->setStatus(TroubleTicket::IN_PROGRESS);

        [$serviceLocatorProphecy, $entityManagerProphecy] = $this->prepareCommonPostUpdateStubs();

        $troubleTicketManagerProphecy = $this->prophesize(TroubleTicketManager::class);
        $troubleTicketManagerProphecy->getDefaultAssignee($troubleTicket)->shouldBeCalledTimes(1)->willReturn($defaultAssignee);
        $serviceLocatorProphecy->get(TroubleTicketManager::class)->shouldBeCalledTimes(1)->willReturn($troubleTicketManagerProphecy->reveal());

        $listener = new TroubleTicketListener($serviceLocatorProphecy->reveal());
        $listener->onPostUpdate($this->buildUpdateEvent($troubleTicket, $previousData));

        self::assertSame(TroubleTicket::PENDING_MOO, $troubleTicket->getStatus());
        self::assertSame($defaultAssignee, $troubleTicket->assignee);
    }

    public function testUpdateModuleChangeByNewModuleMooSkipsPendingMoo(): void
    {
        $oldMoo = new People();
        $oldGku = new People();
        $oldModule = $this->buildModule($oldMoo, $oldGku, true);

        $newMoo = new People();
        $newGku = new People();
        $newModule = $this->buildModule($newMoo, $newGku, true);

        // The creator of the ticket is being changed at the same time to the new module's MOO
        // (as reported: module + creator both changed, type unchanged).
        $type = $this->buildType(Type::REQUEST);

        $previousData = $this->buildTroubleTicket(null, $oldModule, new People());
        $previousData->type = $type;
        $previousData->assignee = $oldMoo;

        $troubleTicket = $this->buildTroubleTicket(null, $newModule, $newMoo);
        $troubleTicket->type = $type;
        $troubleTicket->setStatus(TroubleTicket::PENDING_MOO);
        $troubleTicket->assignee = $oldMoo;

        [$serviceLocatorProphecy, $entityManagerProphecy] = $this->prepareCommonPostUpdateStubs();
        $serviceLocatorProphecy->get(TroubleTicketManager::class)->shouldNotBeCalled();

        $listener = new TroubleTicketListener($serviceLocatorProphecy->reveal());
        $listener->onPostUpdate($this->buildUpdateEvent($troubleTicket, $previousData));

        self::assertSame(TroubleTicket::PENDING, $troubleTicket->getStatus());
        self::assertNull($troubleTicket->assignee);
    }

    public function testUpdateWithNoTypeOrModuleChangeDoesNotRecomputeStatus(): void
    {
        $moo = new People();
        $gku = new People();
        $module = $this->buildModule($moo, $gku, true);
        $type = $this->buildType(Type::REQUEST);

        $previousData = $this->buildTroubleTicket(null, $module, new People());
        $previousData->type = $type;
        $previousData->assignee = $moo;

        $troubleTicket = $this->buildTroubleTicket(null, $module, new People());
        $troubleTicket->type = $type;
        $troubleTicket->setStatus(TroubleTicket::PENDING_MOO);
        $troubleTicket->assignee = $moo;

        [$serviceLocatorProphecy, $entityManagerProphecy] = $this->prepareCommonPostUpdateStubs();
        $serviceLocatorProphecy->get(TroubleTicketManager::class)->shouldNotBeCalled();

        $listener = new TroubleTicketListener($serviceLocatorProphecy->reveal());
        $listener->onPostUpdate($this->buildUpdateEvent($troubleTicket, $previousData));

        // Neither type nor module changed: status/assignee must be left untouched.
        self::assertSame(TroubleTicket::PENDING_MOO, $troubleTicket->getStatus());
        self::assertSame($moo, $troubleTicket->assignee);
    }

    // --------------------------------------------------------------------
    // helpers
    // --------------------------------------------------------------------

    private function buildModule(People $operationalOwner, People $keyUser, bool $misRelative): Module
    {
        $module = new Module();
        $module->setOperationalOwner($operationalOwner);
        $module->setKeyUser($keyUser);
        $module->setMisRelative($misRelative);

        return $module;
    }

    private function buildType(string $type): Type
    {
        $typeEntity = new Type();
        $typeEntity->type = $type;
        $typeEntity->description = $type;

        return $typeEntity;
    }

    private function buildTroubleTicket(?string $type, Module $module, ?People $createdBy): TroubleTicket
    {
        $troubleTicket = new TroubleTicket();
        if (null !== $type) {
            $troubleTicket->type = $this->buildType($type);
        }
        $troubleTicket->module = $module;
        $troubleTicket->createdBy = $createdBy;

        return $troubleTicket;
    }

    private function buildCreateEvent(TroubleTicket $troubleTicket): ViewEvent
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        return new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $troubleTicket);
    }

    private function buildUpdateEvent(TroubleTicket $troubleTicket, TroubleTicket $previousData): ViewEvent
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);
        $request->attributes->set('previous_data', $previousData);

        return new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $troubleTicket);
    }

    /**
     * @return array{0: \Prophecy\Prophecy\ObjectProphecy, 1: \Prophecy\Prophecy\ObjectProphecy}
     */
    private function prepareCommonPostUpdateStubs(): array
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->persist(Argument::any())->willReturn(null);
        $entityManagerProphecy->flush()->willReturn(null);

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->willReturn(new People());

        // A default-assignee re-computation may trigger an "assigned" notification;
        // stub the pieces it needs so tests don't have to care whether it fires.
        $recipientsFinderProphecy = $this->prophesize(RecipientsFinder::class);
        $assignedNotificationFactoryProphecy = $this->prophesize(TroubleTicketAssignedNotificationFactory::class);
        $assignedNotificationFactoryProphecy->createNotification(Argument::any(), Argument::any())->willReturn($this->prophesize(Notification::class)->reveal());

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(EntityManagerInterface::class)->willReturn($entityManagerProphecy->reveal());
        $serviceLocatorProphecy->get(WorkflowStatusUpdater::class)->willReturn($workflowStatusUpdaterProphecy->reveal());
        $serviceLocatorProphecy->get(Security::class)->willReturn($securityProphecy->reveal());
        $serviceLocatorProphecy->get(RecipientsFinder::class)->willReturn($recipientsFinderProphecy->reveal());
        $serviceLocatorProphecy->get(TroubleTicketAssignedNotificationFactory::class)->willReturn($assignedNotificationFactoryProphecy->reveal());

        return [$serviceLocatorProphecy, $entityManagerProphecy];
    }
}
