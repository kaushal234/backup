<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

class RelationDiscrFilter extends DiscrFilter
{
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (self::PARAMETER_DISCRIMINATOR !== $property) {
            return;
        }

        $relationProperty = $this->properties[$property] ?? null;
        if (null === $relationProperty) {
            parent::filterProperty($property, $value, $queryBuilder, $queryNameGenerator, $resourceClass, $operation, $context);

            return;
        }

        $values = $this->normalizeValues((array) $value);
        if ([] === $values) {
            return;
        }

        $metadata = $this->managerRegistry->getManager()->getClassMetadata(
            $this->managerRegistry->getManager()->getClassMetadata($resourceClass)->getAssociationTargetClass($relationProperty)
        );

        $discriminatorMap = $metadata->discriminatorMap ?? [];
        if ([] === $discriminatorMap) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $joinAlias = $queryNameGenerator->generateJoinAlias($relationProperty);

        $instanceOfClauses = [];
        foreach ($values as $v) {
            if (isset($discriminatorMap[$v])) {
                $instanceOfClauses[] = \sprintf('%s INSTANCE OF %s', $joinAlias, $discriminatorMap[$v]);
            }
        }

        if ([] === $instanceOfClauses) {
            return;
        }

        $queryBuilder->andWhere(
            $queryBuilder->expr()->exists(
                \sprintf(
                    'SELECT 1 FROM App\Entity\User %s WHERE %s.position = %s AND (%s)',
                    $joinAlias,
                    $joinAlias,
                    $alias,
                    implode(' OR ', $instanceOfClauses)
                )
            )
        );
    }
}
