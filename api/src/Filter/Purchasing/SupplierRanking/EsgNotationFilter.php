<?php

declare(strict_types=1);

namespace App\Filter\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Purchasing\SupplierRanking\Notation;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use Doctrine\ORM\QueryBuilder;

/**
 * Enables filtering supplier rankings by ESG criterion notation.
 *
 * Example:
 * - ?criteria_7=1
 * - ?criteria_7=null
 */
final class EsgNotationFilter implements FilterInterface
{
    private const string ESG_NOTATION_PROPERTY = 'criteria_7';
    private const int ESG_CRITERIA_ID = 7;

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (SupplierRanking::class !== $resourceClass) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0] ?? null;
        if (null === $rootAlias) {
            return;
        }

        $rawValues = $context['filters'][self::ESG_NOTATION_PROPERTY] ?? null;

        if (null === $rawValues) {
            return;
        }

        if (!\is_array($rawValues)) {
            $rawValues = [$rawValues];
        }

        $notationValues = [];
        $containsNull = false;

        foreach ($rawValues as $value) {
            if ('null' === (string) $value) {
                $containsNull = true;
                continue;
            }

            if (is_numeric($value)) {
                $value = (int) $value;

                if ($value >= 1 && $value <= 5) {
                    $notationValues[] = $value;
                }
            }
        }

        if ([] === $notationValues && !$containsNull) {
            return;
        }

        $subAlias = $queryNameGenerator->generateJoinAlias('esg_notation');
        $criteriaParam = $queryNameGenerator->generateParameterName('esg_criteria_id');
        $notationParam = $queryNameGenerator->generateParameterName('esg_notation_values');

        $subQb = $queryBuilder->getEntityManager()->createQueryBuilder();
        $subQb
            ->select('1')
            ->from(Notation::class, $subAlias)
            ->where(\sprintf('%s.supplierRanking = %s', $subAlias, $rootAlias))
            ->andWhere(\sprintf('IDENTITY(%s.criteria) = :%s', $subAlias, $criteriaParam));

        $conditions = [];

        if ($containsNull) {
            $conditions[] = \sprintf('%s.notation IS NULL', $subAlias);
        }

        if ([] !== $notationValues) {
            $conditions[] = \sprintf('%s.notation IN (:%s)', $subAlias, $notationParam);
            $queryBuilder->setParameter($notationParam, array_unique($notationValues));
        }

        $subQb->andWhere('('.implode(' OR ', $conditions).')');

        $queryBuilder
            ->andWhere(\sprintf('EXISTS (%s)', $subQb->getDQL()))
            ->setParameter($criteriaParam, self::ESG_CRITERIA_ID);
    }

    public function getDescription(string $resourceClass): array
    {
        if (SupplierRanking::class !== $resourceClass) {
            return [];
        }

        return [
            self::ESG_NOTATION_PROPERTY => [
                'property' => self::ESG_NOTATION_PROPERTY,
                'type' => 'array',
                'required' => false,
                'description' => 'ESG notation value (1..5 or "null" for N/A).',
            ],
        ];
    }
}
