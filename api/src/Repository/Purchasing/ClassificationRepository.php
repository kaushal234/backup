<?php

declare(strict_types=1);

namespace App\Repository\Purchasing;

use App\Entity\Purchasing\SupplierRanking\Classification;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class ClassificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Classification::class);
    }

    public function deleteClassificationFromRankings(Classification $classification): void
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->update()
            ->from(SupplierRanking::class, 's')
            ->set('s.classification', ':null')
            ->where('s.classification = :source')
            ->setParameters(new ArrayCollection([
                new Parameter('null', null),
                new Parameter('source', $classification),
            ]))
        ;

        $queryBuilder->getQuery()->execute();
    }
}
