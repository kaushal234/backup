<?php

declare(strict_types=1);

namespace App\Filter\Service;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\TypeInfo\TypeIdentifier;

class ClosureTimeDateFilter implements FilterInterface
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'from' => [
                'property' => 'from',
                'type' => TypeIdentifier::STRING->value,
                'required' => false,
                'description' => 'Start date for closure time report',
            ],
            'salesServiceOrganisation' => [
                'property' => 'salesServiceOrganisation',
                'type' => TypeIdentifier::STRING->value,
                'required' => false,
                'description' => 'SSO for closure time report',
            ],
        ];
    }
}
