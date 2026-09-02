<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use Doctrine\ORM\QueryBuilder;

class DiscrFilter extends AbstractFilter
{
    public const PARAMETER_DISCRIMINATOR = 'discriminator';

    public function getDescription(string $resourceClass): array
    {
        return [
            \sprintf('%s[]', self::PARAMETER_DISCRIMINATOR) => [
                'property' => self::PARAMETER_DISCRIMINATOR,
                'type' => 'string',
                'required' => false,
                'openapi' => new Parameter(name: 'discriminator', in: 'query', description: 'Type of the Doctrine entity in the Inheritance Map (discriminator)'),
                'is_collection' => true,
            ],
        ];
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (self::PARAMETER_DISCRIMINATOR !== $property) {
            return;
        }

        $values = $this->normalizeValues((array) $value);

        if ([] === $values) {
            return;
        }

        $metadata = $this->managerRegistry->getManager()->getClassMetadata($resourceClass);
        /** @var array<string, string> $discriminatorMap */
        $discriminatorMap = $metadata->discriminatorMap ?? [];

        if ([] === $discriminatorMap) {
            return;
        }

        $availableDiscriminators = array_keys($discriminatorMap);

        $validDiscriminatorValues = array_filter(
            $values,
            static fn (string $discriminatorFromRequest): bool => \in_array($discriminatorFromRequest, $availableDiscriminators, true)
        );

        if ([] === $validDiscriminatorValues) {
            return;
        }

        $orX = $queryBuilder->expr()->orX();
        $alias = $queryBuilder->getRootAliases()[0];

        foreach ($validDiscriminatorValues as $validDiscriminatorValue) {
            $orX->add(
                $queryBuilder->expr()->isInstanceOf($alias, $discriminatorMap[$validDiscriminatorValue])
            );
        }

        $queryBuilder->andWhere($orX);
    }

    protected function normalizeValues(array $values): ?array
    {
        foreach ($values as $key => $value) {
            if (!\is_int($key) || !\is_string($value)) {
                unset($values[$key]);
            }
        }

        return array_values($values);
    }
}
