<?php

declare(strict_types=1);

namespace App\Repository\Manufacturing;

use App\Entity\Manufacturing\LeadTime;
use App\Entity\Sales\ProductFamily;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LeadTimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeadTime::class);
    }

    public function getIdentifiersForProductFamily(ProductFamily $productFamily): array
    {
        $qb = $this->createQueryBuilder('lt');

        $qb
            ->select('lt.id')
            ->where('lt.productFamily = :productFamily')
            ->setParameter('productFamily', $productFamily)
        ;

        return $qb->getQuery()->getScalarResult();
    }
}
