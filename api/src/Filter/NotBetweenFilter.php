<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Common\Filter\DateFilterInterface;
use ApiPlatform\Doctrine\Common\Filter\DateFilterTrait;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\Operation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class NotBetweenFilter extends AbstractFilter implements DateFilterInterface
{
    use DateFilterTrait;

    public const DOCTRINE_DATE_TYPES = [
        Types::DATE_MUTABLE => true,
        Types::DATETIME_MUTABLE => true,
        Types::DATETIMETZ_MUTABLE => true,
        Types::TIME_MUTABLE => true,
        Types::DATE_IMMUTABLE => true,
        Types::DATETIME_IMMUTABLE => true,
        Types::DATETIMETZ_IMMUTABLE => true,
        Types::TIME_IMMUTABLE => true,
    ];

    /**
     * @var string
     */
    final public const PARAMETER_NAME = 'not_between';

    /**
     * @var string
     */
    final public const SEPARATOR = ';';

    /**
     * @var string
     */
    final public const INCLUDE_NULL = 'include_null';

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];

        foreach (array_keys($this->properties) as $propertiesPair) {
            if (!\is_string($propertiesPair)) {
                continue;
            }
            $properties = explode(self::SEPARATOR, $propertiesPair);

            if (
                2 !== \count($properties)
                || !$this->isPropertyMapped($properties[0], $resourceClass)
                || !$this->isPropertyMapped($properties[1], $resourceClass)
                || !$this->isDateField($properties[0], $resourceClass)
                || !$this->isDateField($properties[1], $resourceClass)
            ) {
                continue;
            }
            $description[\sprintf('%s[%s]', self::PARAMETER_NAME, $propertiesPair)] = [
                'property' => $propertiesPair,
                'type' => 'string',
                'required' => false,
            ];
        }

        return $description;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (self::PARAMETER_NAME !== $property || !\is_array($value)) {
            return;
        }

        foreach (array_keys($value) as $propertiesPair) {
            /** @var string $propertiesPair */
            $properties = explode(self::SEPARATOR, $propertiesPair);
            if (
                2 !== \count($properties)
                || !$this->isPropertyEnabled($propertiesPair, $resourceClass)
                || !$this->isPropertyMapped($properties[0], $resourceClass)
                || !$this->isPropertyMapped($properties[1], $resourceClass)
                || !$this->isDateField($properties[0], $resourceClass)
                || !$this->isDateField($properties[1], $resourceClass)
            ) {
                continue;
            }
            [$start, $end] = $properties;

            $alias = $queryBuilder->getRootAliases()[0];
            $startAlias = $queryBuilder->getRootAliases()[0];
            $endAlias = $queryBuilder->getRootAliases()[0];

            $startField = $start;
            $endField = $end;

            if ($this->isPropertyNested($start, $resourceClass)) {
                [$startAlias, $startField] = $this->addLeftJoinsForNestedProperty($start, $alias, $queryBuilder, $queryNameGenerator, $resourceClass);
            }

            if ($this->isPropertyNested($end, $resourceClass)) {
                [$endAlias, $endField] = $this->addLeftJoinsForNestedProperty($end, $alias, $queryBuilder, $queryNameGenerator, $resourceClass);
            }

            if ($startAlias !== $endAlias) {
                $this->logger->notice('Invalid filter ignored', [
                    'exception' => new InvalidArgumentException(\sprintf('The fields "%s" and "%s" are in a separate table.', $startField, $endField)),
                ]);

                return;
            }

            $startType = $this->getDoctrineFieldType($start, $resourceClass);
            $endType = $this->getDoctrineFieldType($end, $resourceClass);

            $nullManagement = $this->properties[$propertiesPair] ?? null;

            $this->addOrWhere($queryBuilder, $queryNameGenerator, $startAlias, $startField, $endField, $value[$propertiesPair], $startType, $endType, $nullManagement);
        }
    }

    /**
     * Adds the necessary left joins for a nested property.
     *
     * @return array An array where the first element is the join $alias of the leaf entity,
     *               the second element is the $field name
     *               the third element is the $associations array
     *
     * @throws InvalidArgumentException If property is not nested
     */
    protected function addLeftJoinsForNestedProperty(string $property, string $rootAlias, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass): array
    {
        $propertyParts = $this->splitPropertyParts($property, $resourceClass);
        $parentAlias = $rootAlias;
        foreach ($propertyParts['associations'] as $association) {
            $alias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $parentAlias, $association, Join::LEFT_JOIN);
            $parentAlias = $alias;
        }

        if (!isset($alias)) {
            throw new InvalidArgumentException(\sprintf('Cannot add joins for property "%s" - property is not nested.', $property));
        }

        return [$alias, $propertyParts['field'], $propertyParts['associations']];
    }

    protected function isDateField(string $property, string $resourceClass): bool
    {
        return isset(self::DOCTRINE_DATE_TYPES[(string) $this->getDoctrineFieldType($property, $resourceClass)]);
    }

    /**
     * Adds the where clause according to the chosen null management.
     *
     * @param string|Types $startType
     * @param string|Types $endType
     */
    private function addOrWhere(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $alias, string $startField, string $endField, string $value, $startType, $endType, ?string $nullManagement = null): void
    {
        try {
            $startValue = false === mb_strpos((string) $startType, '_immutable') ? new \DateTime($value) : new \DateTimeImmutable($value);
        } catch (\Exception $exception) {
            // Silently ignore this filter if it can not be transformed to a \DateTime
            $this->logger->notice('Invalid filter ignored', [
                'exception' => new InvalidArgumentException(\sprintf('The field "%s" has a wrong date format. Use one accepted by the \DateTime constructor', $startField)),
            ]);

            return;
        }

        $endValue = false === mb_strpos((string) $endType, '_immutable') ? new \DateTime($value) : new \DateTimeImmutable($value);

        $operatorValue = [
            DateFilterInterface::PARAMETER_BEFORE => '<=',
            DateFilterInterface::PARAMETER_STRICTLY_BEFORE => '<',
            DateFilterInterface::PARAMETER_AFTER => '>=',
            DateFilterInterface::PARAMETER_STRICTLY_AFTER => '>',
        ];

        $startValueParameter = $queryNameGenerator->generateParameterName($startField);
        $endValueParameter = $queryNameGenerator->generateParameterName($endField);

        $startWhere = \sprintf('%s.%s %s :%s', $alias, $startField, $operatorValue[DateFilterInterface::PARAMETER_STRICTLY_AFTER], $startValueParameter);
        $endWhere = \sprintf('%s.%s %s :%s', $alias, $endField, $operatorValue[DateFilterInterface::PARAMETER_STRICTLY_BEFORE], $endValueParameter);

        $expr = $queryBuilder->expr()->orX(
            $startWhere,
            $endWhere
        );

        if (self::INCLUDE_NULL === $nullManagement) {
            $expr->add(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->isNull(\sprintf('%s.%s', $alias, $startField)),
                    $queryBuilder->expr()->isNull(\sprintf('%s.%s', $alias, $endField))
                )
            );
        }

        $queryBuilder->andWhere($expr);

        $queryBuilder->setParameter($startValueParameter, $startValue, (string) $startType);
        $queryBuilder->setParameter($endValueParameter, $endValue, (string) $endType);
    }
}
