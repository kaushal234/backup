<?php

declare(strict_types=1);

namespace App\Repository\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FirstArticleQualificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FirstArticleQualification::class);
    }

    public function findFAQForReminderDateCommand()
    {
        $qb = $this->createQueryBuilder('q');

        return $qb
            ->where('q.planDefinitionCompletedAt is NULL')
            ->orWhere('q.completedAt is NULL')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findFAQDueDatePassed()
    {
        $qb = $this->createQueryBuilder('q');

        return $qb
            ->where('q.dueDate = :yesterday')
            ->setParameter('yesterday', (new \DateTime('yesterday'))->format('Y-m-d'))
            ->getQuery()
            ->getResult()
        ;
    }
}
