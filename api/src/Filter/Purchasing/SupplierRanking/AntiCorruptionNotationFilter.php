<?php

declare(strict_types=1);

namespace App\Filter\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Purchasing\SupplierRanking\Notation;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Enables filtering supplier rankings by Anti-Corruption criterion notation.
 *
 * Example:
 * - ?criteria_8=1
 * - ?criteria_8=null
 */
final class AntiCorruptionNotationFilter implements FilterInterface
{
    private const string ANTI_CORRUPTION_NOTATION_PROPERTY = 'criteria_8';
    private const int ANTI_CORRUPTION_CRITERIA_ID = 8;

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (SupplierRanking::class !== $resourceClass) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0] ?? null;
        if (null === $rootAlias) {
            return;
        }

        if (!$request->query->has(self::ANTI_CORRUPTION_NOTATION_PROPERTY)) {
            return;
        }

        $rawNotation = $request->query->get(self::ANTI_CORRUPTION_NOTATION_PROPERTY);
        if (!\is_string($rawNotation)) {
            return;
        }

        $notationValue = null;
        $isNullNotation = false;

        if ('null' === $rawNotation) {
            $isNullNotation = true;
        } elseif (is_numeric($rawNotation)) {
            $notationValue = (int) $rawNotation;
            if ($notationValue < 1 || $notationValue > 5) {
                return;
            }
        } else {
            return;
        }

        $subAlias = $queryNameGenerator->generateJoinAlias('anti_corruption_notation');
        $criteriaParam = $queryNameGenerator->generateParameterName('anti_corruption_criteria_id');
        $notationParam = $queryNameGenerator->generateParameterName('anti_corruption_notation_value');

        $subQb = $queryBuilder->getEntityManager()->createQueryBuilder();
        $subQb
            ->select('1')
            ->from(Notation::class, $subAlias)
            ->where(\sprintf('%s.supplierRanking = %s', $subAlias, $rootAlias))
            ->andWhere(\sprintf('IDENTITY(%s.criteria) = :%s', $subAlias, $criteriaParam));

        if ($isNullNotation) {
            $subQb->andWhere(\sprintf('%s.notation IS NULL', $subAlias));
        } else {
            $subQb->andWhere(\sprintf('%s.notation = :%s', $subAlias, $notationParam));

            // Parameters used inside EXISTS subquery must be bound on the root QueryBuilder.
            $queryBuilder->setParameter($notationParam, $notationValue);
        }

        $queryBuilder
            ->andWhere(\sprintf('EXISTS (%s)', $subQb->getDQL()))
            ->setParameter($criteriaParam, self::ANTI_CORRUPTION_CRITERIA_ID);
    }

    public function getDescription(string $resourceClass): array
    {
        if (SupplierRanking::class !== $resourceClass) {
            return [];
        }

        return [
            self::ANTI_CORRUPTION_NOTATION_PROPERTY => [
                'property' => self::ANTI_CORRUPTION_NOTATION_PROPERTY,
                'type' => 'string',
                'required' => false,
                'description' => 'Anti-Corruption notation value (1..5 or "null" for N/A).',
            ],
        ];
    }
}
