<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExchangeCalendarEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExchangeCalendarEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExchangeCalendarEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExchangeCalendarEvent[]    findAll()
 * @method ExchangeCalendarEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExchangeCalendarEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeCalendarEvent::class);
    }
}
