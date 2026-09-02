<?php

declare(strict_types=1);

namespace App\Tests\Command\MIS\Module;

use App\Command\MIS\NotifyEscalatedTroubleTicket;
use App\Entity\AuditLog;
use App\Entity\Common\Notification\Notification;
use App\Entity\Common\Notification\NotificationTemplate;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Factory\Common\Notification\MIS\TroubleTicketAwaitNotificationFactory;
use App\Repository\Common\NotificationRepository;
use App\Repository\MIS\TroubleTicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NotifyEscalatedTroubleTicketCommandTest extends TestCase
{
    /**
     * @dataProvider provideScenarios
     */
    public function testExecuteCommandWithRealEntities(
        array $tickets,
        array $logsPerTicket,
        int $expectedPersistCalls,
        int $expectedFlushCalls,
        bool $notificationExists
    ): void {
        $ticketRepository = $this->createMock(TroubleTicketRepository::class);
        $ticketRepository->method('findAll')->willReturn($tickets);

        $templateMock = $this->createMock(NotificationTemplate::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $auditLogRepository = $this->createMock(EntityRepository::class);
        $auditLogRepository->method('findBy')->willReturnCallback(static function () use (&$logsPerTicket) {
            return array_shift($logsPerTicket);
        });

        $notificationTemplateRepository = $this->createMock(EntityRepository::class);
        $entityManager
            ->expects(self::exactly(2))
            ->method('getRepository')
            ->withConsecutive([NotificationTemplate::class], [AuditLog::class])
            ->willReturnOnConsecutiveCalls($notificationTemplateRepository, $auditLogRepository)
        ;

        $notificationTemplateRepository->method('findOneBy')->with()->willReturn($templateMock);

        if ($expectedPersistCalls > 0) {
            $entityManager->expects($this->exactly($expectedPersistCalls))
                ->method('persist')
                ->with($this->isInstanceOf(Notification::class));
        } else {
            $entityManager->expects($this->never())->method('persist');
        }

        $entityManager->expects($this->exactly($expectedFlushCalls))->method('flush');

        $notificationFactory = $this->createMock(TroubleTicketAwaitNotificationFactory::class);
        $notificationFactory->method('createNotification')->willReturn(new Notification());

        $notificationRepo = $this->createMock(NotificationRepository::class);
        $notificationRepo->method('existsForRecipientReferenceAndTemplate')->willReturn($notificationExists);

        $command = new NotifyEscalatedTroubleTicket(
            $ticketRepository,
            $entityManager,
            $notificationFactory,
            $notificationRepo
        );

        $status = $command->run(
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $this->assertSame(0, $status);
    }

    public static function provideScenarios(): \Generator
    {
        yield 'no tickets' => [
            'tickets' => [],
            'logsPerTicket' => [],
            'expectedPersistCalls' => 0,
            'expectedFlushCalls' => 1,
            'notificationExists' => false,
        ];

        yield 'wrong status' => [
            'tickets' => [self::createTicket('closed', 1, null)],
            'logsPerTicket' => [],
            'expectedPersistCalls' => 0,
            'expectedFlushCalls' => 1,
            'notificationExists' => false,
        ];

        yield 'no logs' => [
            'tickets' => [self::createTicket(TroubleTicket::AWAITING_USER, 1, null)],
            'logsPerTicket' => [[]],
            'expectedPersistCalls' => 0,
            'expectedFlushCalls' => 1,
            'notificationExists' => false,
        ];

        yield 'log too recent' => [
            'tickets' => [self::createTicket(TroubleTicket::AWAITING_USER, 1, null)],
            'logsPerTicket' => [self::createLogs('-10 days', TroubleTicket::AWAITING_USER)],
            'expectedPersistCalls' => 0,
            'expectedFlushCalls' => 1,
            'notificationExists' => false,
        ];

        yield 'assignee null' => [
            'tickets' => [self::createTicket(TroubleTicket::AWAITING_USER, 1, null)],
            'logsPerTicket' => [self::createLogs('-40 days', TroubleTicket::AWAITING_USER)],
            'expectedPersistCalls' => 0,
            'expectedFlushCalls' => 1,
            'notificationExists' => false,
        ];

        yield 'valid notification case (not exists)' => [
            'tickets' => [self::createTicket(
                TroubleTicket::AWAITING_USER,
                1,
                self::createUserWithSupervisor()
            )],
            'logsPerTicket' => [self::createLogs('-40 days', TroubleTicket::AWAITING_USER)],
            'expectedPersistCalls' => 2,  // assignee + supervisor
            'expectedFlushCalls' => 1,
            'notificationExists' => false,
        ];

        yield 'already exists -> no persist' => [
            'tickets' => [self::createTicket(
                TroubleTicket::AWAITING_USER,
                1,
                self::createUserWithSupervisor()
            )],
            'logsPerTicket' => [self::createLogs('-40 days', TroubleTicket::AWAITING_USER)],
            'expectedPersistCalls' => 0,
            'expectedFlushCalls' => 1,
            'notificationExists' => true,
        ];
    }

    private static function createTicket(string $status, int $id, ?People $assignee = null): TroubleTicket
    {
        $ticket = new TroubleTicket();
        $reflectionProperty = new \ReflectionProperty(TroubleTicket::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($ticket, $id);
        $ticket->setStatus($status);
        $ticket->assignee = $assignee;

        return $ticket;
    }

    private static function createLogs(string $dateStr, string $value): array
    {
        $log = new AuditLog();
        $log->createdAt = new \DateTime($dateStr);
        $log->value = $value;

        return [$log];
    }

    private static function createUserWithSupervisor(): People
    {
        $supervisor = new People();
        $user = new People();
        $user->setSupervisor($supervisor);

        return $user;
    }
}
