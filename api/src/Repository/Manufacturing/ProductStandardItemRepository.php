<?php

declare(strict_types=1);

namespace App\Repository\Manufacturing;

use App\Entity\Manufacturing\ManufacturingFamily;
use App\Entity\Manufacturing\ProductStandardItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class ProductStandardItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductStandardItem::class);
    }

    public function findStandardItemsByManufacturingFamilyAndErp(ManufacturingFamily $manufacturingFamily, array $erps): array
    {
        $qb = $this->createQueryBuilder('ppn');
        $qb
            ->select('ppn.standardItem')
            ->distinct()
            ->join('ppn.product', 'p')
            ->join('ppn.factory', 'f')
            ->andWhere('p.manufacturingFamily = :family')
            ->andWhere($qb->expr()->in('f.erp', ':erps'))
            ->setParameters(new ArrayCollection([
                new Parameter('family', $manufacturingFamily),
                new Parameter('erps', $erps),
            ]))
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function findPartNumbersByErp(array $erps): array
    {
        $qb = $this->createQueryBuilder('ppn');
        $qb
            ->select('ppn.standardItem')
            ->distinct()
            ->join('ppn.product', 'p')
            ->join('ppn.factory', 'f')
            ->andWhere($qb->expr()->in('f.erp', ':erps'))
            ->setParameters(new ArrayCollection([
                new Parameter('erps', $erps),
            ]))
        ;

        return $qb->getQuery()->getScalarResult();
    }
}
