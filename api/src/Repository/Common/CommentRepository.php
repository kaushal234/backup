<?php

declare(strict_types=1);

namespace App\Repository\Common;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly IriConverterInterface $iriConverter
    ) {
        parent::__construct($registry, Comment::class);
    }

    public function findCommentsForEntity(object $entity): array
    {
        $queryBuilder = $this->createQueryBuilder('c');

        $queryBuilder
            ->where('c.resource = :entity')
            ->setParameter('entity', $this->iriConverter->getIriFromResource($entity))
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
