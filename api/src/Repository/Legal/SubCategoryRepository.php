<?php

declare(strict_types=1);

namespace App\Repository\Legal;

use App\Entity\Legal\Category;
use App\Entity\Legal\SubCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SubCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubCategory::class);
    }

    public function getIdentifiersForCategory(Category $category): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.id')
            ->where('s.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getArrayResult();
    }
}
