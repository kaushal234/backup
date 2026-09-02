<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Entity\EmissionRating;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

final class EmissionRatingExtension implements QueryCollectionExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (EmissionRating::class !== $resourceClass || !$operation instanceof GetCollection) {
            return;
        }

        if (\in_array(EmissionRating::SHOW_OBSOLETE, $context[AbstractNormalizer::GROUPS] ?? [], true)) {
            return;
        }

        $queryBuilder
            ->andWhere(\sprintf('%s.obsolete = :value', $queryBuilder->getRootAliases()[0]))
            ->setParameter('value', false)
        ;
    }
}
