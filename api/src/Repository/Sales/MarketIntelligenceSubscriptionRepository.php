<?php

declare(strict_types=1);

namespace App\Repository\Sales;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Division;
use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Entity\Directory\PositionLevel;
use App\Entity\Directory\Region;
use App\Entity\Directory\SubDivision;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Entity\Sales\MarketIntelligence\MarketIntelligenceSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class MarketIntelligenceSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarketIntelligenceSubscription::class);
    }

    /**
     * @return array|People[]
     */
    public function findSubscriberForMarketIntelligence(MarketIntelligence $marketIntelligence): array
    {
        $qb = $this->getEntityManager()->getRepository(People::class)->createQueryBuilder('p');

        $qb
            ->leftJoin(MarketIntelligenceSubscription::class, 'm', Join::WITH, 'm.subscriber = p.id')
            ->where('m.competitor IN (:competitors) OR m.competitor IS NULL')
            ->andWhere('m.customer IN (:customers) OR m.customer IS NULL')
            ->andWhere('m.productType IN (:productTypes) OR m.productType IS NULL')
            ->andWhere('m.type = :type OR m.type IS NULL')
            ->andWhere('p.hidden = :hidden')
            ->andWhere('p.disabled = :disabled')
            ->andWhere('m.subscriber = p.id')
            ->setParameter('customers', $marketIntelligence->getCustomers())
            ->setParameter('productTypes', $marketIntelligence->getProductTypes())
            ->setParameter('competitors', $marketIntelligence->getCompetitors())
            ->setParameter('type', $marketIntelligence->getType())
            ->setParameter('hidden', false)
            ->setParameter('disabled', false)
        ;

        if (!$marketIntelligence->getDivisions()->isEmpty()) {
            $qb
                ->leftJoin(BusinessUnit::class, 'bu', Join::WITH, 'p.businessUnit = bu')
                ->leftJoin(Region::class, 'region', Join::WITH, 'bu.region = region')
                ->leftJoin(SubDivision::class, 'sub_division', Join::WITH, 'region.subDivision = sub_division')
                ->leftJoin(Division::class, 'division', Join::WITH, 'sub_division.division = division')
                ->andWhere($qb->expr()->in('division', ':divisions'))
                ->setParameter('divisions', $marketIntelligence->getDivisions())
            ;
        }

        if (!$marketIntelligence->getPositionLevels()->isEmpty()) {
            $qb
                ->leftJoin(Position::class, 'position', Join::WITH, 'p.position = position')
                ->leftJoin(PositionLevel::class, 'position_level', Join::WITH, 'position.level = position_level')
                ->andWhere($qb->expr()->in('position_level', ':positionLevels'))
                ->setParameter('positionLevels', $marketIntelligence->getPositionLevels())
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
