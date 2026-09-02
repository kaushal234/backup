<?php

declare(strict_types=1);

namespace App\Repository\Sales;

use App\Entity\Continent;
use App\Entity\Country;
use App\Entity\Directory\Network;
use App\Entity\Sales\SalesArea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class SalesAreaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SalesArea::class);
    }

    /**
     * @return SalesArea[]
     */
    public function findByCountryAndNetwork(Country $country, Network $network): array
    {
        $qb = $this->createQueryBuilder('sa');

        $qb
            ->join('sa.sso', 'l')
            ->where('l.network = :network')
            ->andWhere('sa.country = :country')
            ->setParameters(new ArrayCollection([
                new Parameter('network', $network),
                new Parameter('country', $country),
            ]))
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return SalesArea[]
     */
    public function findByContinent(Continent $continent): array
    {
        $qb = $this->createQueryBuilder('sa');

        $qb
            ->join('sa.country', 'c')
            ->where('c.continent = :continent')
            ->setParameters(new ArrayCollection([
                new Parameter('continent', $continent),
            ]))
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return SalesArea[]
     */
    public function findWithPublicCountry(): array
    {
        $qb = $this->createQueryBuilder('sa');
        $qb->join('sa.country', 'c')
            ->andWhere('c.public = :public')
            ->setParameter('public', true)
        ;

        return $qb->getQuery()->getResult();
    }
}
