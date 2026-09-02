<?php

declare(strict_types=1);

namespace App\Repository\News;

use App\Entity\News\News;
use App\Entity\News\NewsCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    public function getIdentifiersForNewsCategory(NewsCategory $newsCategory)
    {
        $qb = $this->createQueryBuilder('n');

        $qb
            ->select('n.id')
            ->where('n.category = :newscategory')
            ->setParameter('newscategory', $newsCategory)
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function findLatestNewsByCategoryFilter(?string $categoryName = null, bool $exclude = false, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('n')
            ->join('n.category', 'c')
            ->orderBy('n.date', 'DESC');

        if (null !== $categoryName) {
            if ($exclude) {
                $qb->where('c.name != :categoryName');
            } else {
                $qb->where('c.name = :categoryName');
            }
            $qb->setParameter('categoryName', $categoryName);
        }

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
