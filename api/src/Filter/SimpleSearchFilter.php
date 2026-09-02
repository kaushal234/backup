<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Common\Filter\SearchFilterTrait;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\IdentifiersExtractorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class SimpleSearchFilter extends AbstractFilter implements SearchFilterInterface
{
    use SearchFilterTrait;

    public function __construct(ManagerRegistry $managerRegistry, IriConverterInterface $iriConverter, ?PropertyAccessorInterface $propertyAccessor = null, ?LoggerInterface $logger = null, ?array $properties = null, ?IdentifiersExtractorInterface $identifiersExtractor = null, ?NameConverterInterface $nameConverter = null)
    {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);

        $this->iriConverter = $iriConverter;
        $this->identifiersExtractor = $identifiersExtractor;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            'q' => [
                'property' => 'q',
                'type' => 'string',
                'required' => false,
                'strategy' => 'none',
            ],
        ];
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getIdentifiersExtractor(): ?IdentifiersExtractorInterface
    {
        return $this->identifiersExtractor;
    }

    public function getIriConverter(): IriConverterInterface
    {
        return $this->iriConverter;
    }

    public function getPropertyAccessor(): PropertyAccessorInterface|\Symfony\Component\PropertyAccess\PropertyAccessor
    {
        return $this->propertyAccessor;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if ('q' !== $property) {
            return;
        }

        $parts = explode(' ', (string) $value);

        foreach ($parts as $part) {
            if ('' === $part) {
                continue;
            }

            $nestedOrParts = [];

            foreach ($this->properties as $property => $strategy) {
                $alias = $queryBuilder->getAllAliases()[0];
                $field = $property;

                if (
                    !$this->isPropertyEnabled($property, $resourceClass)
                    || !$this->isPropertyMapped($property, $resourceClass, true)
                ) {
                    return;
                }

                if ($this->isPropertyNested($property, $resourceClass)) {
                    [$alias, $field, $associations] = $this->addJoinsForNestedProperty($property, $alias, $queryBuilder, $queryNameGenerator, $resourceClass, Join::LEFT_JOIN);
                }

                $nestedOrParts[] = $this->orWhereByStrategy($strategy, $queryBuilder, $alias, $field, $part, $queryNameGenerator);
            }

            $queryBuilder->andWhere(implode(' OR ', $nestedOrParts));
        }
    }

    protected function getType(string $doctrineType): string
    {
        return match ($doctrineType) {
            Types::JSON => 'array',
            Types::BIGINT, Types::INTEGER, Types::SMALLINT => 'int',
            Types::BOOLEAN => 'bool',
            Types::DATE_MUTABLE, Types::TIME_MUTABLE, Types::DATETIME_MUTABLE, Types::DATETIMETZ_MUTABLE, Types::DATE_IMMUTABLE, Types::TIME_IMMUTABLE, Types::DATETIME_IMMUTABLE, Types::DATETIMETZ_IMMUTABLE => \DateTimeInterface::class,
            Types::FLOAT => 'float',
            default => 'string',
        };
    }

    private function orWhereByStrategy($strategy, QueryBuilder $queryBuilder, $alias, $field, $value, QueryNameGeneratorInterface $queryNameGenerator)
    {
        $valueParameter = $queryNameGenerator->generateParameterName($field);

        switch ($strategy) {
            case null:
            case SearchFilter::STRATEGY_EXACT:
                if ('id' === $field) {
                    $value = $this->getFilterValueFromUrl($value);
                }

                $queryBuilder->setParameter($valueParameter, $value);

                return \sprintf('%s.%s = :%s', $alias, $field, $valueParameter);
            case SearchFilter::STRATEGY_PARTIAL:
                $queryBuilder->setParameter($valueParameter, \sprintf('%%%s%%', $value));

                return \sprintf('%s.%s LIKE :%s', $alias, $field, $valueParameter);
            case SearchFilter::STRATEGY_START:
                $queryBuilder->setParameter($valueParameter, \sprintf('%s%%', $value));

                return \sprintf('%s.%s LIKE :%s', $alias, $field, $valueParameter);
            case SearchFilter::STRATEGY_END:
                $queryBuilder->setParameter($valueParameter, \sprintf('%%%s', $value));

                return \sprintf('%s.%s LIKE :%s', $alias, $field, $valueParameter);
            case SearchFilter::STRATEGY_WORD_START:
                $queryBuilder->setParameter(\sprintf('%s_1', $valueParameter), \sprintf('%s%%', $value))
                    ->setParameter(\sprintf('%s_2', $valueParameter), \sprintf('%% %s%%', $value));

                return \sprintf('%1$s.%2$s LIKE :%3$s_1 OR %1$s.%2$s LIKE :%3$s_2', $alias, $field, $valueParameter);
        }

        throw new InvalidArgumentException(\sprintf('strategy %s does not exist.', $strategy));
    }

    /**
     * Gets the ID from an URI or a raw ID.
     *
     * @param string $value
     *
     * @return string
     */
    private function getFilterValueFromUrl($value)
    {
        try {
            $item = $this->iriConverter->getResourceFromIri($value);
            $value = $this->propertyAccessor->getValue($item, 'id');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            // Do nothing, return the raw value
        }

        return $value;
    }
}
