<?php

declare(strict_types=1);

namespace App\Tests\Command\MIS;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Command\MIS\AutoEscalateSolutionProposedTroubleTicketCommand;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Notifier\MIS\TroubleTicket\TroubleTicketNotifier;
use App\Repository\MIS\TroubleTicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AutoEscalateSolutionProposedTroubleTicketCommandTest extends TestCase
{
    public function testNoTicketsDoesNothing(): void
    {
        $repository = $this->createMock(TroubleTicketRepository::class);
        $repository->method('findTroubleTicketsToAutoEscalate')->willReturn([]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->with(TroubleTicket::class)->willReturn($repository);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $notifier = $this->createMock(TroubleTicketNotifier::class);
        $notifier->expects(self::never())->method('sendNotification');

        $command = new AutoEscalateSolutionProposedTroubleTicketCommand(
            $entityManager,
            $this->createMock(IriConverterInterface::class),
            $notifier,
        );

        $status = $command->run(
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class),
        );

        self::assertSame(0, $status);
    }

    public function testTicketWithoutSupervisorIsSkipped(): void
    {
        $assignee = new People();
        $ticket = $this->createTicket(1, $assignee);

        $repository = $this->createMock(TroubleTicketRepository::class);
        $repository->method('findTroubleTicketsToAutoEscalate')->willReturn([$ticket]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->with(TroubleTicket::class)->willReturn($repository);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $notifier = $this->createMock(TroubleTicketNotifier::class);
        $notifier->expects(self::never())->method('sendNotification');

        $command = new AutoEscalateSolutionProposedTroubleTicketCommand(
            $entityManager,
            $this->createMock(IriConverterInterface::class),
            $notifier,
        );

        $status = $command->run(
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class),
        );

        self::assertSame(0, $status);
        self::assertFalse($ticket->autoEscalated);
        self::assertNull($ticket->autoEscalatedAt);
        self::assertSame($assignee, $ticket->assignee);
    }

    public function testTicketIsEscalatedAndNotified(): void
    {
        $supervisor = new People();
        $assignee = new People();
        $assignee->setFirstname('John');
        $assignee->setLastname('Doe');
        $assignee->setSupervisor($supervisor);
        $ticket = $this->createTicket(42, $assignee);

        $repository = $this->createMock(TroubleTicketRepository::class);
        $repository->method('findTroubleTicketsToAutoEscalate')->willReturn([$ticket]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->with(TroubleTicket::class)->willReturn($repository);

        $persisted = [];
        $entityManager->expects(self::exactly(2))
            ->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$persisted): void {
                $persisted[] = $entity;
            });
        $entityManager->expects(self::once())->method('flush');

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getIriFromResource')->with($ticket)->willReturn('/trouble_tickets/42');

        $notifier = $this->createMock(TroubleTicketNotifier::class);
        $notifier->expects(self::once())
            ->method('sendNotification')
            ->with($ticket, 'auto_escalate', null, ['previous_assignee_fullname' => $assignee->getDisplayName()], false);

        $command = new AutoEscalateSolutionProposedTroubleTicketCommand(
            $entityManager,
            $iriConverter,
            $notifier,
        );

        $status = $command->run(
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class),
        );

        self::assertSame(0, $status);
        self::assertSame($supervisor, $ticket->assignee);
        self::assertTrue($ticket->autoEscalated);
        self::assertInstanceOf(\DateTimeInterface::class, $ticket->autoEscalatedAt);
        self::assertCount(2, $persisted);
        self::assertSame($ticket, $persisted[0]);
        self::assertInstanceOf(Comment::class, $persisted[1]);
        self::assertSame('/trouble_tickets/42', $persisted[1]->getResource());
    }

    private function createTicket(int $id, ?People $assignee): TroubleTicket
    {
        $ticket = new TroubleTicket();
        $reflectionProperty = new \ReflectionProperty(TroubleTicket::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($ticket, $id);
        $ticket->assignee = $assignee;

        return $ticket;
    }
}
