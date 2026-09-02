<?php

declare(strict_types=1);

namespace App\Filter\SalesForecast;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\CustomerType;
use App\Entity\Sales\SalesForecast;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SalesForecastMilitaryFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_MILITARY_PROPERTY = 'military';

    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
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

        if (!$request->query->has(static::FILTER_MILITARY_PROPERTY)) {
            return;
        }

        $value = $request->query->get(static::FILTER_MILITARY_PROPERTY);

        if (SalesForecast::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Sales Forecasts resource');
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $buyerAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'buyer', Join::LEFT_JOIN);
        $buyerTypeAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $buyerAlias, 'customerTypes', Join::LEFT_JOIN);
        $endUserAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'endUser', Join::LEFT_JOIN);
        $endUserTypeAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $endUserAlias, 'customerTypes', Join::LEFT_JOIN);

        if (\in_array($value, [true, 'true', '1'], true)) {
            $orStatements = $queryBuilder->expr()->orX();
            $orStatements->add(\sprintf('%s.name = :military', $buyerTypeAlias));
            $orStatements->add(\sprintf('%s.name = :military', $endUserTypeAlias));
            $queryBuilder->andWhere($orStatements);
        }

        if (\in_array($value, [false, 'false', '0'], true)) {
            $queryBuilder->andWhere(\sprintf('%s.name != :military', $buyerTypeAlias));
            $queryBuilder->andWhere(\sprintf('%s.name != :military', $endUserTypeAlias));
        }

        $queryBuilder->setParameter('military', CustomerType::MILITARY_TYPE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_MILITARY_PROPERTY => [
                'property' => static::FILTER_MILITARY_PROPERTY,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}
