<?php

declare(strict_types=1);

namespace App\Filter\Support\EquipmentRecord;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

class EquipmentRecordByEndUserOrBuyerFilter extends AbstractFilter
{
    private const BUYER_OR_USER_NAME = 'buyer_or_endUser_name';

    public function getDescription(string $resourceClass): array
    {
        return [
            self::BUYER_OR_USER_NAME => [
                'property' => 'buyer.name or endUser.name',
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Search by buyer.name or endUser.name',
                    'name' => 'Buyer or EndUser Name',
                    'type' => 'Search',
                ],
            ],
        ];
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (!$value || !$this->isPropertyEnabled($property, $resourceClass)) {
            return;
        }

        if (self::BUYER_OR_USER_NAME === $property) {
            $alias = $queryBuilder->getRootAliases()[0];

            $queryBuilder
                ->leftJoin(\sprintf('%s.endUser', $alias), 'endUserAlias')
                ->leftJoin(\sprintf('%s.buyer', $alias), 'buyerAlias');

            $orX = new Orx();
            $orX->add('endUserAlias.name = :name');
            $orX->add('buyerAlias.name = :name');

            $queryBuilder
                ->andWhere($orX)
                ->setParameter('name', $value);
        }
    }
}
