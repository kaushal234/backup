<?php

declare(strict_types=1);

namespace App\Command\MIS;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:mis:assign_trouble_ticket', description: 'Assign Trouble Ticket for of deactivated user')]
class AssignTroubleTicketOfDeactivatedUser extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IriConverterInterface $iriConverter,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach (['assignee', 'misAssignee'] as $people) {
            $qb = $this->entityManager->createQueryBuilder();
            $qb
                ->select('t')
                ->from(TroubleTicket::class, 't')
                ->innerJoin(People::class, 'p', Join::WITH, "t.$people = p.id")
                ->where($qb->expr()->eq('p.disabled', 'true'))
                ->andWhere($qb->expr()->in('t.status', ':openStatus'))
                ->setParameter('openStatus', TroubleTicket::OPEN_STATUSES);

            $results = $qb->getQuery()->getResult();

            /** @var TroubleTicket $ticket */
            foreach ($results as $ticket) {
                $supervisor = $ticket->{$people}->getSupervisor();
                if (null === $supervisor) {
                    continue;
                }
                $supervisorIsDisabled = $supervisor->isDisabled();
                while ($supervisorIsDisabled) {
                    $supervisor = $supervisor->getSupervisor();
                    if (null === $supervisor) {
                        continue 2;
                    }
                    $supervisorIsDisabled = $supervisor->isDisabled();
                }
                $ticket->{$people} = $supervisor;
                $this->entityManager->persist($ticket);

                $comment = (new Comment())
                    ->setMessage(\sprintf('The new %s of the ticket is %s', $people, $supervisor->getDisplayName()))
                    ->setResource($this->iriConverter->getIriFromResource($ticket))
                ;
                $this->entityManager->persist($comment);
            }
        }
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
