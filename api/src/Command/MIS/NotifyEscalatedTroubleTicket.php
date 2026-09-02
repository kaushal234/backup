<?php

declare(strict_types=1);

namespace App\Command\MIS;

use App\Entity\AuditLog;
use App\Entity\Common\Notification\NotificationTemplate;
use App\Entity\Common\Notification\NotificationTemplateList;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Factory\Common\Notification\MIS\TroubleTicketAwaitNotificationFactory;
use App\Repository\Common\NotificationRepository;
use App\Repository\MIS\TroubleTicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:mis:notify_escalated_trouble_ticket', description: 'Notify that Trouble Ticket is awaiting user and no action is made.')]
class NotifyEscalatedTroubleTicket extends Command
{
    public function __construct(
        private readonly TroubleTicketRepository $troubleTicketRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TroubleTicketAwaitNotificationFactory $troubleTicketAwaitNotificationFactory,
        private readonly NotificationRepository $notificationRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $notificationRepository = $this->entityManager->getRepository(NotificationTemplate::class);
        $auditLogRepository = $this->entityManager->getRepository(AuditLog::class);
        $template = $notificationRepository->findOneBy(['name' => NotificationTemplateList::TROUBLE_TICKET_AWAIT_USER->value]);

        /** @var TroubleTicket $troubleTicket */
        foreach ($this->troubleTicketRepository->findAll() as $troubleTicket) {
            if (!\in_array($troubleTicket->getStatus(), [TroubleTicket::AWAITING_USER, TroubleTicket::AWAITING_USER_MOO], true)) {
                continue;
            }

            if (0 === \count($logs = $auditLogRepository->findBy(
                [
                    'referenceId' => $troubleTicket->getId(),
                    'auditType' => 'trouble_ticket',
                ],
                ['createdAt' => 'DESC'],
                1
            ))) {
                continue;
            }

            /** @var AuditLog $log */
            $log = $logs[0];

            if (!\in_array($log->value, [TroubleTicket::AWAITING_USER, TroubleTicket::AWAITING_USER_MOO], true) || $log->createdAt > new \DateTime('30 days ago') || null === $troubleTicket->assignee) {
                continue;
            }

            foreach ([$troubleTicket->assignee, $troubleTicket->assignee->getSupervisor()] as $recipient) {
                if (!$recipient) {
                    continue;
                }

                if ($this->notificationRepository->existsForRecipientReferenceAndTemplate(
                    $recipient,
                    $troubleTicket->getId(),
                    $template
                )) {
                    continue;
                }

                $notification = $this->troubleTicketAwaitNotificationFactory->createNotification($troubleTicket, $recipient);
                $this->entityManager->persist($notification);
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
