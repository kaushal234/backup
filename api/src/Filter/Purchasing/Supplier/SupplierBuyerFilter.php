<?php

declare(strict_types=1);

namespace App\Filter\Purchasing\Supplier;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\TypeInfo\TypeIdentifier;

class SupplierBuyerFilter implements FilterInterface
{
    final public const SUPPLIER_PROPERTY_NAME = 'supplier';
    final public const FILTER_BUYER_PROPERTY = 'buyer';

    public function __construct(
        public readonly RequestStack $requestStack,
        public readonly IriConverterInterface $iriConverter
    ) {
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (!property_exists($resourceClass, self::SUPPLIER_PROPERTY_NAME)) {
            throw new \Exception(\sprintf('Missing supplier property with name `%s`.', self::SUPPLIER_PROPERTY_NAME));
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return;
        }

        $filteredBuyer = null;
        if ($request->query->has(self::FILTER_BUYER_PROPERTY)) {
            $filteredBuyer = $this->iriConverter->getResourceFromIri(
                $request->query->get(self::FILTER_BUYER_PROPERTY)
            );
        }

        if ($filteredBuyer) {
            $parameterName = $queryNameGenerator->generateParameterName(self::FILTER_BUYER_PROPERTY);
            $queryBuilder->innerJoin('o.supplier', 'supplier');
            $queryBuilder->innerJoin('supplier.buyFrom', 'buyFrom', 'WITH', 'supplier.location = buyFrom.location');
            $queryBuilder->andWhere(\sprintf('buyFrom.buyer IN (:%s)', $parameterName));
            $queryBuilder->setParameter($parameterName, $filteredBuyer);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::FILTER_BUYER_PROPERTY => [
                'property' => self::FILTER_BUYER_PROPERTY,
                'type' => TypeIdentifier::NULL->value,
                'required' => false,
            ],
        ];
    }
}
