<?php

declare(strict_types=1);

namespace App\Repository\Common;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class ActivityRepository extends ServiceEntityRepository
{
    private readonly IriConverterInterface $iriConverter;

    public function __construct(ManagerRegistry $registry, IriConverterInterface $iriConverter)
    {
        parent::__construct($registry, Activity::class);
        $this->iriConverter = $iriConverter;
    }

    public function findActivitiesForEntity(object $entity): array
    {
        $queryBuilder = $this->createQueryBuilder('a');

        $queryBuilder
            ->where('a.resource = :entity')
            ->setParameter('entity', $this->iriConverter->getIriFromResource($entity))
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function replaceActivity(object $previousResource, object $newResource)
    {
        $qb = $this->createQueryBuilder('a');

        $qb->update()
            ->set('a.resource', ':newResource')
            ->where('a.resource = :previousResource')
            ->setParameters(new ArrayCollection([
                new Parameter('newResource', $this->iriConverter->getIriFromResource($newResource)),
                new Parameter('previousResource', $this->iriConverter->getIriFromResource($previousResource)),
            ]))
        ;

        $qb->getQuery()->execute();
    }
}
