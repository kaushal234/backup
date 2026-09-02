<?php

declare(strict_types=1);

namespace App\Repository\HumanResources;

use App\Entity\HumanResources\Job;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class JobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

    /**
     * @return Job[]
     */
    public function findPublic()
    {
        $qb = $this->createQueryBuilder('j');

        $qb
            ->andWhere('j.enabled = :enabled')
            ->andWhere('j.synchronized = :synchronized')
            ->setParameters(new ArrayCollection([
                new Parameter('enabled', true),
                new Parameter('synchronized', true),
            ]));

        return $qb->getQuery()->getResult();
    }

    public function findLatestJobs(?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('j')
            ->orderBy('j.createdAt', 'DESC');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
