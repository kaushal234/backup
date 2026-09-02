<?php

declare(strict_types=1);

namespace App\Repository\MIS;

use App\Entity\MIS\TroubleTicket\TroubleTicket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TroubleTicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TroubleTicket::class);
    }

    /**
     * @return TroubleTicket[]
     */
    public function findTroubleTicketsToAutoEscalate(int $firstEscalationDays, int $reEscalationDays): array
    {
        $qb = $this->createQueryBuilder('tts');

        $qb
            ->where($qb->expr()->in('tts.status', ':statuses'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->eq('tts.autoEscalated', ':notEscalated'),
                    $qb->expr()->eq('tts.solutionProposedAt', ':firstDate'),
                ),
                $qb->expr()->andX(
                    $qb->expr()->eq('tts.autoEscalated', ':escalated'),
                    $qb->expr()->eq('tts.autoEscalatedAt', ':reDate'),
                ),
            ))
            ->setParameter('statuses', [TroubleTicket::SOLUTION_PROPOSED, TroubleTicket::SOLUTION_PROPOSED_MOO])
            ->setParameter('notEscalated', false)
            ->setParameter('escalated', true)
            ->setParameter('firstDate', (new \DateTime(\sprintf('%d days ago', $firstEscalationDays)))->format('Y-m-d'))
            ->setParameter('reDate', (new \DateTime(\sprintf('%d days ago', $reEscalationDays)))->format('Y-m-d'))
        ;

        return $qb->getQuery()->getResult();
    }
}
