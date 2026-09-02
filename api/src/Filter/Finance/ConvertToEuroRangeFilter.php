<?php

declare(strict_types=1);

namespace App\Filter\Finance;

use ApiPlatform\Doctrine\Common\Filter\RangeFilterInterface;
use ApiPlatform\Doctrine\Common\Filter\RangeFilterTrait;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Finance\ExchangeRate;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class ConvertToEuroRangeFilter extends AbstractFilter implements RangeFilterInterface
{
    use RangeFilterTrait;

    /**
     * Adds the where clause according to the operator.
     *
     * @param string $alias
     * @param string $field
     * @param string $operator
     * @param string $value
     */
    protected function addWhere(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, $alias, $field, $operator, $value)
    {
        $valueParameter = $queryNameGenerator->generateParameterName($field);
        $currencyAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $queryBuilder->getRootAliases()[0], 'currency', Join::LEFT_JOIN);

        $exchangeRateRepository = $this->managerRegistry->getRepository(ExchangeRate::class);
        $condition = $exchangeRateRepository->getIfConditionForExchangeRates($currencyAlias);

        switch ($operator) {
            case self::PARAMETER_BETWEEN:
                $rangeValue = explode('..', $value);

                $rangeValue = $this->normalizeBetweenValues($rangeValue);
                if (null === $rangeValue) {
                    return;
                }

                $queryBuilder
                    ->andWhere(\sprintf('%1$s.%2$s BETWEEN :%3$s_1 AND :%3$s_2', $alias, $field, $valueParameter))
                    ->setParameter(\sprintf('%s_1', $valueParameter), $rangeValue[0])
                    ->setParameter(\sprintf('%s_2', $valueParameter), $rangeValue[1]);

                break;
            case self::PARAMETER_GREATER_THAN:
                $value = $this->normalizeValue($value, $operator);
                if (null === $value) {
                    return;
                }

                $queryBuilder
                    ->andWhere(\sprintf('%s.%s > (:%s*(%s))', $alias, $field, $valueParameter, $condition))
                    ->setParameter($valueParameter, $value);

                break;
            case self::PARAMETER_GREATER_THAN_OR_EQUAL:
                $value = $this->normalizeValue($value, $operator);
                if (null === $value) {
                    return;
                }

                $queryBuilder
                    ->andWhere(\sprintf('%s.%s >= (:%s*(%s))', $alias, $field, $valueParameter, $condition))
                    ->setParameter($valueParameter, $value);

                break;
            case self::PARAMETER_LESS_THAN:
                $value = $this->normalizeValue($value, $operator);
                if (null === $value) {
                    return;
                }

                $queryBuilder
                    ->andWhere(\sprintf('%s.%s < (:%s*(%s))', $alias, $field, $valueParameter, $condition))
                    ->setParameter($valueParameter, $value);

                break;
            case self::PARAMETER_LESS_THAN_OR_EQUAL:
                $value = $this->normalizeValue($value, $operator);
                if (null === $value) {
                    return;
                }

                $queryBuilder
                    ->andWhere(\sprintf('%s.%s <= (:%s*(%s))', $alias, $field, $valueParameter, $condition))
                    ->setParameter($valueParameter, $value);

                break;
        }
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (
            !\is_array($value)
            || !$this->isPropertyEnabled($property, $resourceClass)
            || !$this->isPropertyMapped($property, $resourceClass)
        ) {
            return;
        }

        $values = $this->normalizeValues($value, $property);
        if (null === $values) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $field = $property;

        if ($this->isPropertyNested($property, $resourceClass)) {
            [$alias, $field] = $this->addJoinsForNestedProperty($property, $alias, $queryBuilder, $queryNameGenerator, $resourceClass, Join::INNER_JOIN);
        }

        foreach ($values as $operator => $value) {
            $this->addWhere(
                $queryBuilder,
                $queryNameGenerator,
                $alias,
                $field,
                $operator,
                $value
            );
        }
    }
}
