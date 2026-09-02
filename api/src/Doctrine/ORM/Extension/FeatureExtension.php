<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Entity\Feature;
use Doctrine\ORM\QueryBuilder;

class FeatureExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (Feature::class !== $resourceClass || !$operation instanceof GetCollection) {
            return;
        }

        if (($context['filters']['normalization_groups_override'] ?? []) === ['feature_list']) {
            $queryBuilder->distinct();
        }
    }
}
