<?php

declare(strict_types=1);

namespace App\Repository\Report;

use App\Entity\Report\ReportSnapshot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class ReportSnapshotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportSnapshot::class);
    }

    public function findSnapshot(string $resource, string $x, string $y, array $options, \DateTime $date): ?ReportSnapshot
    {
        $qb = $this->createQueryBuilder('r');

        $qb
            ->andWhere('r.resource = :resource')
            ->andWhere('r.x = :x')
            ->andWhere('r.y = :y')
            ->andWhere('r.options = :options')
            ->andWhere('r.createdAt > :start_date')
            ->andWhere('r.createdAt < :end_date')
            ->orderBy('r.createdAt', 'DESC')
            ->addOrderBy('r.id', 'DESC')
            ->setMaxResults(1)
            ->setParameters(new ArrayCollection([
                new Parameter('resource', $resource),
                new Parameter('x', $x),
                new Parameter('y', $y),
                new Parameter('options', json_encode(array_filter($options, static fn ($item) => '' !== (string) $item), \JSON_THROW_ON_ERROR)),
                new Parameter('start_date', $date->format('Y-m-01 00:00:00')),
                new Parameter('end_date', $date->format('Y-m-t 23:59:59')),
            ]))
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
