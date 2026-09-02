<?php

declare(strict_types=1);

namespace App\Repository\Common;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Doctrine\Change;
use App\Entity\Activity\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class LogRepository extends ServiceEntityRepository
{
    private readonly IriConverterInterface $iriConverter;

    public function __construct(ManagerRegistry $registry, IriConverterInterface $iriConverter)
    {
        parent::__construct($registry, Log::class);
        $this->iriConverter = $iriConverter;
    }

    public function findResourceLastUpdate(object $resource)
    {
        return $this->createResourceUpdateQueryBuilder($resource)->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    public function findAllResourceUpdates(object $resource)
    {
        return $this->createResourceUpdateQueryBuilder($resource)->getQuery()->getArrayResult();
    }

    public function findByPeriod(string $discriminator, \DateTime $startDate, \DateTime $endDate)
    {
        return $this->createQueryBuilder('log')
            ->where('log.discriminator = :discriminator')
            ->andWhere('log.createdAt BETWEEN :startDate AND :endDate')
            ->setParameter('discriminator', $discriminator)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    private function createResourceUpdateQueryBuilder(object $resource): QueryBuilder
    {
        return $this->createQueryBuilder('l')
            ->where('l.resource = :resource')
            ->andWhere('l.action = :action')
            ->orderBy('l.createdAt', Criteria::DESC)
            ->setParameters(new ArrayCollection([
                new Parameter('resource', $this->iriConverter->getIriFromResource($resource)),
                new Parameter('action', Change::ACTION_UPDATE),
            ]));
    }
}
