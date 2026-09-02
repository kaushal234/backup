<?php

declare(strict_types=1);

namespace App\Repository\HumanResources;

use App\Entity\HumanResources\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findEventsThisWeek(\DateTimeInterface $startOfWeek, \DateTimeInterface $endOfWeek, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.startedAt BETWEEN :start AND :end')
            ->setParameter('start', $startOfWeek)
            ->setParameter('end', $endOfWeek);

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
