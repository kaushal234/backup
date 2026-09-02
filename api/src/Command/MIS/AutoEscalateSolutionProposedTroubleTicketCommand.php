<?php

declare(strict_types=1);

namespace App\Command\MIS;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Notifier\MIS\TroubleTicket\TroubleTicketNotifier;
use App\Repository\MIS\TroubleTicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:mis:auto_escalate_trouble_ticket', description: 'Auto-escalate Trouble Tickets stuck in SOLUTION PROPOSED to the assignee supervisor.')]
class AutoEscalateSolutionProposedTroubleTicketCommand extends Command
{
    private const FIRST_ESCALATION_DAYS = 30;
    private const RE_ESCALATION_DAYS = 10;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IriConverterInterface $iriConverter,
        private readonly TroubleTicketNotifier $notifier,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var TroubleTicketRepository $repository */
        $repository = $this->entityManager->getRepository(TroubleTicket::class);

        foreach ($repository->findTroubleTicketsToAutoEscalate(self::FIRST_ESCALATION_DAYS, self::RE_ESCALATION_DAYS) as $troubleTicket) {
            $supervisor = $troubleTicket->assignee?->getSupervisor();
            if (null === $supervisor) {
                continue;
            }

            $previousAssignee = $troubleTicket->assignee;

            $troubleTicket->assignee = $supervisor;
            $troubleTicket->autoEscalated = true;
            $troubleTicket->autoEscalatedAt = new \DateTime();

            $this->entityManager->persist($troubleTicket);

            $comment = (new Comment())
                ->setMessage(\sprintf('Trouble Ticket auto-escalated from %s to %s after inactivity in SOLUTION PROPOSED.', $previousAssignee->getDisplayName(), $supervisor->getDisplayName()))
                ->setResource($this->iriConverter->getIriFromResource($troubleTicket))
            ;

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $this->notifier->sendNotification($troubleTicket, 'auto_escalate', null, ['previous_assignee_fullname' => $previousAssignee->getDisplayName()], false);

            $output->writeln(\sprintf('Trouble Ticket #%d auto-escalated to people #%d.', $troubleTicket->getId(), $supervisor->getId()));
        }

        return Command::SUCCESS;
    }
}
