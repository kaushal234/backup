<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Audible\AudibleProvider;
use App\Audible\TroubleTicketAudible;
use App\Doctrine\Change;
use App\Doctrine\Voter\AuditLogVoter;
use App\Entity\AuditLog;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Event\EntityChangeEvent;
use App\EventListener\AuditLogListener;
use App\Tests\Mailer\DummyObject;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class AuditLogListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testAuditLogIsCreated(): void
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $repositoryMock = $this->createMock(EntityRepository::class);
        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $audibleProviderProphecy = $this->prophesize(AudibleProvider::class);
        $troubleTicketAudibleConfigurationProphecy = $this->prophesize(TroubleTicketAudible::class);
        $auditLogVoterProphecy = $this->prophesize(AuditLogVoter::class);
        $securityProphecy = $this->prophesize(Security::class);

        $change = new Change();
        $change->setChangeSet(['status' => [null, 'PENDING']]);
        $change->setEntity($troubleTicket = new TroubleTicket());

        $serviceLocatorProphecy->get(AuditLogVoter::class)->shouldBeCalledOnce()->willReturn($auditLogVoterProphecy->reveal());
        $auditLogVoterProphecy->vote()->shouldBeCalledOnce()->willReturn(true);

        $serviceLocatorProphecy->get(AudibleProvider::class)->shouldBeCalledOnce()->willReturn($audibleProviderProphecy->reveal());
        $audibleProviderProphecy->getAudibleConfiguration('trouble_ticket')->shouldBeCalledOnce()->willReturn($troubleTicketAudibleConfigurationProphecy->reveal());

        $troubleTicketAudibleConfigurationProphecy->getAudibleProperties()->shouldBeCalledOnce()->willReturn(['status']);

        $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldBeCalledOnce()->willReturn($entityManagerProphecy->reveal());
        $entityManagerProphecy->getRepository(AuditLog::class)->shouldBeCalledOnce()->willReturn($repositoryMock);

        $serviceLocatorProphecy->get(PropertyAccessorInterface::class)->shouldBeCalledOnce()->willReturn($propertyAccessorProphecy->reveal());
        $propertyAccessorProphecy->getValue($troubleTicket, 'id')->shouldBeCalledOnce()->willReturn(2);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledOnce()->willReturn($securityProphecy->reveal());
        $securityProphecy->getUser()->shouldBeCalledOnce()->willReturn($people = new People());

        $repositoryMock->expects($this->once())->method('findBy')->with(['auditType' => 'trouble_ticket', 'referenceId' => 2, 'property' => 'status'], ['id' => 'DESC'])->willReturn([$previous = new AuditLog()]);

        $entityManagerProphecy->persist(Argument::that(static function (AuditLog $auditLog) use ($previous, $people) {
            return 2 === $auditLog->referenceId
                && 'trouble_ticket' === $auditLog->auditType
                && $auditLog->getPrevious() === $previous
                && 'PENDING' === $auditLog->value
                && 'status' === $auditLog->property
                && null === $auditLog->next
                && $people === $auditLog->createdBy
            ;
        }))->shouldBeCalledOnce();

        $event = new EntityChangeEvent($change);

        $listener = new AuditLogListener($serviceLocatorProphecy->reveal());
        $listener->onEntityChange($event);
    }

    public function testAuditLogIsCreatedWithNoPrevious(): void
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $repositoryMock = $this->createMock(EntityRepository::class);
        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $audibleProviderProphecy = $this->prophesize(AudibleProvider::class);
        $troubleTicketAudibleConfigurationProphecy = $this->prophesize(TroubleTicketAudible::class);
        $auditLogVoterProphecy = $this->prophesize(AuditLogVoter::class);
        $securityProphecy = $this->prophesize(Security::class);

        $change = new Change();
        $change->setChangeSet(['status' => [null, 'PENDING']]);
        $change->setEntity($troubleTicket = new TroubleTicket());

        $serviceLocatorProphecy->get(AuditLogVoter::class)->shouldBeCalledOnce()->willReturn($auditLogVoterProphecy->reveal());
        $auditLogVoterProphecy->vote()->shouldBeCalledOnce()->willReturn(true);

        $serviceLocatorProphecy->get(AudibleProvider::class)->shouldBeCalledOnce()->willReturn($audibleProviderProphecy->reveal());
        $audibleProviderProphecy->getAudibleConfiguration('trouble_ticket')->shouldBeCalledOnce()->willReturn($troubleTicketAudibleConfigurationProphecy->reveal());

        $troubleTicketAudibleConfigurationProphecy->getAudibleProperties()->shouldBeCalledOnce()->willReturn(['status']);

        $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldBeCalledOnce()->willReturn($entityManagerProphecy->reveal());
        $entityManagerProphecy->getRepository(AuditLog::class)->shouldBeCalledOnce()->willReturn($repositoryMock);

        $serviceLocatorProphecy->get(PropertyAccessorInterface::class)->shouldBeCalledOnce()->willReturn($propertyAccessorProphecy->reveal());
        $propertyAccessorProphecy->getValue($troubleTicket, 'id')->shouldBeCalledOnce()->willReturn(2);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledOnce()->willReturn($securityProphecy->reveal());
        $securityProphecy->getUser()->shouldBeCalledOnce()->willReturn(null);

        $repositoryMock->expects($this->once())->method('findBy')->with(['auditType' => 'trouble_ticket', 'referenceId' => 2, 'property' => 'status'], ['id' => 'DESC'])->willReturn([]);

        $entityManagerProphecy->persist(Argument::that(static function (AuditLog $auditLog) {
            return 2 === $auditLog->referenceId
                && 'trouble_ticket' === $auditLog->auditType
                && null === $auditLog->getPrevious()
                && 'PENDING' === $auditLog->value
                && 'status' === $auditLog->property
                && null === $auditLog->next
                && null === $auditLog->createdBy
            ;
        }))->shouldBeCalledOnce();

        $event = new EntityChangeEvent($change);

        $listener = new AuditLogListener($serviceLocatorProphecy->reveal());
        $listener->onEntityChange($event);
    }

    public function testAuditLogIsNotCreatedWhenChangesetDoesNotMatchConfiguration(): void
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $repositoryMock = $this->createMock(EntityRepository::class);
        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $audibleProviderProphecy = $this->prophesize(AudibleProvider::class);
        $troubleTicketAudibleConfigurationProphecy = $this->prophesize(TroubleTicketAudible::class);
        $auditLogVoterProphecy = $this->prophesize(AuditLogVoter::class);
        $securityProphecy = $this->prophesize(Security::class);

        $change = new Change();
        $change->setChangeSet(['foo' => [null, 'PENDING']]);
        $change->setEntity($troubleTicket = new TroubleTicket());

        $serviceLocatorProphecy->get(AuditLogVoter::class)->shouldBeCalledOnce()->willReturn($auditLogVoterProphecy->reveal());
        $auditLogVoterProphecy->vote()->shouldBeCalledOnce()->willReturn(true);

        $serviceLocatorProphecy->get(AudibleProvider::class)->shouldBeCalledOnce()->willReturn($audibleProviderProphecy->reveal());
        $audibleProviderProphecy->getAudibleConfiguration('trouble_ticket')->shouldBeCalledOnce()->willReturn($troubleTicketAudibleConfigurationProphecy->reveal());

        $troubleTicketAudibleConfigurationProphecy->getAudibleProperties()->shouldBeCalledOnce()->willReturn(['status']);

        $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldBeCalledOnce()->willReturn($entityManagerProphecy->reveal());
        $entityManagerProphecy->getRepository(AuditLog::class)->shouldBeCalledOnce()->willReturn($repositoryMock);

        $serviceLocatorProphecy->get(PropertyAccessorInterface::class)->shouldBeCalledOnce()->willReturn($propertyAccessorProphecy->reveal());
        $propertyAccessorProphecy->getValue($troubleTicket, 'id')->shouldBeCalledOnce()->willReturn(2);

        $serviceLocatorProphecy->get(Security::class)->shouldNotBeCalled();
        $securityProphecy->getUser()->shouldNotBeCalled();

        $repositoryMock->expects($this->never())->method('findBy');
        $entityManagerProphecy->persist(Argument::any())->shouldNotBeCalled();

        $event = new EntityChangeEvent($change);

        $listener = new AuditLogListener($serviceLocatorProphecy->reveal());
        $listener->onEntityChange($event);
    }

    public function testAuditLogIsNotCreatedWhenEntityDoesNotHaveAttribute(): void
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $repositoryMock = $this->createMock(EntityRepository::class);
        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $audibleProviderProphecy = $this->prophesize(AudibleProvider::class);
        $troubleTicketAudibleConfigurationProphecy = $this->prophesize(TroubleTicketAudible::class);
        $auditLogVoterProphecy = $this->prophesize(AuditLogVoter::class);
        $securityProphecy = $this->prophesize(Security::class);

        $change = new Change();
        $change->setChangeSet(['foo' => [null, 'bar']]);
        $change->setEntity($dummy = new DummyObject('foo'));

        $serviceLocatorProphecy->get(AuditLogVoter::class)->shouldBeCalledOnce()->willReturn($auditLogVoterProphecy->reveal());
        $auditLogVoterProphecy->vote()->shouldBeCalledOnce()->willReturn(true);

        $serviceLocatorProphecy->get(AudibleProvider::class)->shouldNotBeCalled();
        $audibleProviderProphecy->getAudibleConfiguration(Argument::any())->shouldNotBeCalled();
        $troubleTicketAudibleConfigurationProphecy->getAudibleProperties()->shouldNotBeCalled();
        $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldNotBeCalled();
        $entityManagerProphecy->getRepository(AuditLog::class)->shouldNotBeCalled();
        $serviceLocatorProphecy->get(PropertyAccessorInterface::class)->shouldNotBeCalled();
        $propertyAccessorProphecy->getValue(Argument::any())->shouldNotBeCalled();
        $repositoryMock->expects($this->never())->method('findBy');
        $serviceLocatorProphecy->get(Security::class)->shouldNotBeCalled();
        $securityProphecy->getUser()->shouldNotBeCalled();
        $entityManagerProphecy->persist(Argument::any())->shouldNotBeCalled();

        $event = new EntityChangeEvent($change);

        $listener = new AuditLogListener($serviceLocatorProphecy->reveal());
        $listener->onEntityChange($event);
    }
}
