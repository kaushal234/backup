<?php

declare(strict_types=1);

namespace App\Filter\Purchasing\Supplier;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\TypeInfo\TypeIdentifier;

class SupplierMasterBuyerOrderFilter implements FilterInterface
{
    private const ORDER_PARAMETER = 'order';
    private const ORDER_PROPERTY = 'supplier.masterBuyer.lastname';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return;
        }

        $order = $request->query->all(self::ORDER_PARAMETER);

        $direction = $order[self::ORDER_PROPERTY] ?? null;
        if (!\is_string($direction)) {
            return;
        }

        $direction = mb_strtoupper($direction);
        if (!\in_array($direction, ['ASC', 'DESC'], true)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $supplierAlias = 'supplierMasterBuyer';
        $buyFromAlias = 'buyFromMasterBuyer';
        $buyerAlias = 'masterBuyer';

        $queryBuilder
            ->leftJoin($rootAlias.'.supplier', $supplierAlias)
            ->leftJoin(
                $supplierAlias.'.buyFrom',
                $buyFromAlias,
                'WITH',
                $buyFromAlias.'.location = '.$supplierAlias.'.location'
            )
            ->leftJoin($buyFromAlias.'.buyer', $buyerAlias)
            ->addOrderBy($buyerAlias.'.lastname', $direction)
            ->addOrderBy($rootAlias.'.id', 'ASC');
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'order['.self::ORDER_PROPERTY.']' => [
                'property' => self::ORDER_PROPERTY,
                'type' => TypeIdentifier::STRING->value,
                'required' => false,
            ],
        ];
    }
}
