<?php

declare(strict_types=1);

namespace App\Filter\Directory;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

class OrLocationCapabilityFilter extends AbstractFilter
{
    public function getDescription(string $resourceClass): array
    {
        return [
            'has_any_capability[]' => [
                'property' => 'has_any_capability',
                'type' => 'array',
                'required' => false,
                'swagger' => [
                    'description' => 'Filters rentals with at least one of the specified capacities (OR logic)',
                    'name' => 'has_any_capability[]',
                    'type' => 'array',
                ],
            ],
        ];
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if ('has_any_capability' !== $property) {
            return;
        }

        if (!\is_array($value)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $orExpr = $queryBuilder->expr()->orX();

        foreach ($value as $capabilityName) {
            if (\in_array($capabilityName, ['sso', 'factory', 'warehouse', 'sparePartsHub', 'serviceHub', 'headQuarter'], true)) {
                $paramName = 'values_'.$capabilityName;
                $orExpr->add($queryBuilder->expr()->eq("$alias.capability.$capabilityName", ":$paramName"));
                $queryBuilder->setParameter($paramName, true);
            }
        }

        if ($orExpr->count() > 0) {
            $queryBuilder->andWhere($orExpr);
        }
    }
}
