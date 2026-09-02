<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\Location;
use Doctrine\ORM\QueryBuilder;

class LocationExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (Location::class !== $resourceClass || !$operation instanceof GetCollection) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $businessUnit = $queryNameGenerator->generateJoinAlias('businessUnit');
        $queryBuilder
            ->addSelect($businessUnit)
            ->leftJoin(\sprintf('%s.businessUnit', $rootAlias), $businessUnit);
    }
}
