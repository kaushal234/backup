<?php

declare(strict_types=1);

namespace App\Repository\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\PlanItemType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PlanItemTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlanItemType::class);
    }
}
