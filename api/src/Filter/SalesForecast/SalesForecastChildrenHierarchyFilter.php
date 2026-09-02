<?php

declare(strict_types=1);

namespace App\Filter\SalesForecast;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\Customer;
use App\Entity\Sales\SalesForecast;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SalesForecastChildrenHierarchyFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_CHILDREN_HIERARCHY_PROPERTY = 'customer_with_children';

    protected IriConverterInterface $iriConverter;

    protected RequestStack $requestStack;

    public function __construct(IriConverterInterface $iriConverter, RequestStack $requestStack)
    {
        $this->iriConverter = $iriConverter;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(static::FILTER_CHILDREN_HIERARCHY_PROPERTY)) {
            return;
        }

        $value = $request->query->get(static::FILTER_CHILDREN_HIERARCHY_PROPERTY);

        if (SalesForecast::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Sales Forecasts resource');
        }

        /** @var Customer $customer */
        $customer = $this->iriConverter->getResourceFromIri($value);

        $orStatements = $queryBuilder->expr()->orX();

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->leftJoin(\sprintf('%s.buyer', $alias), 'sfr_buyer_children_level_1');
        $orStatements->add($queryBuilder->expr()->eq('sfr_buyer_children_level_1.id', $customer->getId()));

        for ($i = 2; $i <= 3; ++$i) {
            $condition = \sprintf('sfr_buyer_children_level_%s.parentCustomer', $i - 1).\sprintf('= sfr_buyer_children_level_%s.id', $i);
            $queryBuilder->leftJoin(Customer::class, \sprintf('sfr_buyer_children_level_%s', $i), Join::WITH, $condition);
            $orStatements->add($queryBuilder->expr()->eq(\sprintf('sfr_buyer_children_level_%s', $i), $customer->getId()));
        }
        $queryBuilder->andWhere($orStatements);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_CHILDREN_HIERARCHY_PROPERTY => [
                'property' => static::FILTER_CHILDREN_HIERARCHY_PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}
