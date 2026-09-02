<?php

declare(strict_types=1);

namespace App\Filter\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use Doctrine\ORM\QueryBuilder;

/**
 * Enables sorting on computed nextReviewAt (lastReviewAt + periodicity months).
 */
class NextReviewAtOrderFilter implements FilterInterface
{
    private const string PARAMETER_NAME = 'order[nextReviewAt]';

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (SupplierRanking::class !== $resourceClass) {
            return;
        }

        $direction = mb_strtolower((string) ($context['filters']['order']['nextReviewAt'] ?? ''));
        if (!\in_array($direction, ['asc', 'desc'], true)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0] ?? null;
        if (null === $rootAlias) {
            return;
        }

        $classificationAlias = $queryNameGenerator->generateJoinAlias('classification');
        $periodicityAlias = $queryNameGenerator->generateJoinAlias('periodicity');
        $orderAlias = $queryNameGenerator->generateParameterName('nextReviewAtOrder');

        $queryBuilder->leftJoin(\sprintf('%s.classification', $rootAlias), $classificationAlias);
        $queryBuilder->leftJoin(
            \sprintf('%s.periodicityByExpertiseLevels', $classificationAlias),
            $periodicityAlias,
            'WITH',
            \sprintf('%s.expertiseLevel = %s.expertiseLevel', $periodicityAlias, $rootAlias)
        );

        $queryBuilder->addSelect(
            \sprintf("DATE_ADD(%s.lastReviewAt, %s.months, 'month') AS HIDDEN %s", $rootAlias, $periodicityAlias, $orderAlias)
        );
        $queryBuilder->addOrderBy($orderAlias, mb_strtoupper($direction));
    }

    public function getDescription(string $resourceClass): array
    {
        if (SupplierRanking::class !== $resourceClass) {
            return [];
        }

        return [
            self::PARAMETER_NAME => [
                'property' => 'nextReviewAt',
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Sort by computed next review date (asc|desc).',
                ],
            ],
        ];
    }
}
