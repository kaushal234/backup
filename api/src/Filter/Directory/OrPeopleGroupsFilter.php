<?php

declare(strict_types=1);

namespace App\Filter\Directory;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class OrPeopleGroupsFilter implements FilterInterface
{
    private const FILTER_PEOPLE_GROUPS_NAME = 'has_groups';

    public function getDescription(string $resourceClass): array
    {
        return [
            self::FILTER_PEOPLE_GROUPS_NAME.'[]' => [
                'property' => self::FILTER_PEOPLE_GROUPS_NAME,
                'type' => 'array',
                'required' => false,
            ],
        ];
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (People::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the People resource');
        }

        $request = $context['request'] ?? null;

        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(self::FILTER_PEOPLE_GROUPS_NAME)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->join(\sprintf('%s.acls', $rootAlias), 'acl')
            ->join('acl.group', 'group')
            ->andWhere($queryBuilder->expr()->in('group.name', ':group'))
            ->setParameter('group', $request->query->all(self::FILTER_PEOPLE_GROUPS_NAME))
        ;
    }
}
