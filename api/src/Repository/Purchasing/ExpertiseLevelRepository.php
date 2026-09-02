<?php

declare(strict_types=1);

namespace App\Repository\Purchasing;

use App\Entity\Purchasing\SupplierRanking\ExpertiseLevel;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class ExpertiseLevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExpertiseLevel::class);
    }

    public function deleteExpertiseLevelFromRankings(ExpertiseLevel $expertiseLevel): void
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->update()
            ->from(SupplierRanking::class, 's')
            ->set('s.expertiseLevel', ':null')
            ->where('s.expertiseLevel = :source')
            ->setParameters(new ArrayCollection([
                new Parameter('null', null),
                new Parameter('source', $expertiseLevel),
            ]))
        ;

        $queryBuilder->getQuery()->execute();
    }
}
