<?php

declare(strict_types=1);

namespace App\Repository\Survey;

use App\Entity\Survey\Answer;
use App\Entity\Survey\Item;
use App\Entity\Survey\PublishedSurvey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PublishedSurveyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublishedSurvey::class);
    }

    public function isItemFullyAnswered(PublishedSurvey $publishedSurvey, Item $item): bool
    {
        $qb = $this->getQueryBuilderForAnsweredItems($publishedSurvey->getId());
        $qb->select('count(DISTINCT concat(IDENTITY(a.item), IDENTITY(a.ratingType)))')
            ->distinct(true)
            ->andWhere('IDENTITY(a.item) = :index_item')
            ->setParameter(':index_item', $item->getId())
            ->orderBy('IDENTITY(a.item)')
        ;
        $query = $qb->getQuery();
        $queryResult = (int) $query->getSingleScalarResult();

        if (0 === $queryResult || $queryResult !== $publishedSurvey->getCampaign()->getModel()->getRatingTypes()->count()) {
            return false;
        }

        return true;
    }

    /**
     * @return PublishedSurvey[]
     */
    public function getUnsentSurveys(?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('ps');

        $qb
            ->join('ps.campaign', 'c')
            ->andWhere('ps.sent = :sent')
            ->andWhere('c.sentAt IS NOT NULL')
            ->setParameter('sent', false)
        ;

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    private function getQueryBuilderForAnsweredItems(int $publishedSurveyId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('IDENTITY(a.item) as item_id, IDENTITY(a.ratingType) as rating_type_id')
            ->distinct(true)
            ->from(Answer::class, 'a')
            ->where('a.publishedSurvey=:publishedSurveyId')
            ->setParameter(':publishedSurveyId', $publishedSurveyId)
            ->orderBy('item_id, rating_type_id')
        ;

        return $qb;
    }
}
