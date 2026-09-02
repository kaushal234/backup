<?php

declare(strict_types=1);

namespace App\Repository\Common;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Common\Subscription;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class SubscriptionRepository extends ServiceEntityRepository
{
    private readonly IriConverterInterface $iriConverter;

    public function __construct(ManagerRegistry $registry, IriConverterInterface $iriConverter)
    {
        parent::__construct($registry, Subscription::class);
        $this->iriConverter = $iriConverter;
    }

    /**
     * @return Subscription[]
     */
    public function findByResource(object $resource)
    {
        $queryBuilder = $this
            ->createQueryBuilder('f')
            ->join('f.user', 'u')
            ->where('f.resource = :resource')
            ->andWhere('u.disabled = :disabled')
            ->setParameters(new ArrayCollection([
                new Parameter('resource', $this->iriConverter->getIriFromResource($resource)),
                new Parameter('disabled', false),
            ]))
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function isFollowingResource(User $user, object $resource): bool
    {
        $queryBuilder = $this
            ->createQueryBuilder('f')
            ->where('f.resource = :resource')
            ->andWhere('f.user = :user')
            ->setParameters(new ArrayCollection([
                new Parameter('resource', $this->iriConverter->getIriFromResource($resource)),
                new Parameter('user', $user),
            ]));

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findByResourceClassAndUser(User $user, string $resourceClass): array
    {
        $iri = $this->iriConverter->getIriFromResource($resourceClass, UrlGeneratorInterface::ABS_PATH, new GetCollection());
        $queryBuilder = $this
            ->createQueryBuilder('f');
        $queryBuilder
            ->where($queryBuilder->expr()->like('f.resource', ':iri'))
            ->andWhere('f.user = :user')
            ->setParameters(new ArrayCollection([
                new Parameter('iri', $iri.'%'),
                new Parameter('user', $user),
            ]));

        return $queryBuilder->getQuery()->getResult();
    }
}
