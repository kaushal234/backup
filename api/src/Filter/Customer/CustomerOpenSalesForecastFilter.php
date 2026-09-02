<?php

declare(strict_types=1);

namespace App\Filter\Customer;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\Customer;
use App\Entity\Sales\SalesForecast;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerOpenSalesForecastFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_OPEN__SALES_FORECAST_PROPERTY = 'open_sales_forecast';
    /**
     * @var array
     */
    final public const OPEN_STATUSES = [SalesForecast::DELAYED, SalesForecast::IN_PROGRESS, SalesForecast::BUDGET];

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

        if (!$request->query->has(static::FILTER_OPEN__SALES_FORECAST_PROPERTY)) {
            return;
        }

        $value = $request->query->get(static::FILTER_OPEN__SALES_FORECAST_PROPERTY);

        if (Customer::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Customer resource');
        }

        $queryBuilder->leftJoin(SalesForecast::class, 'sfr', Join::WITH, 'o.id = sfr.buyer');

        if (\in_array($value, [true, 'true', '1'], true)) {
            $orStatements = $queryBuilder->expr()->orX();
            $orStatements->add($queryBuilder->expr()->gt('sfr.closedAt', ':one_month_ago'));

            $queryBuilder->setParameter('one_month_ago', new \DateTime('1 month ago'));
            $orStatements->add($queryBuilder->expr()->in('sfr.status', ':open_statuses'));

            $queryBuilder->setParameter('open_statuses', self::OPEN_STATUSES);
            $queryBuilder->andWhere($orStatements);

            return;
        }

        if (\in_array($value, [false, 'false', '0'], true)) {
            $subQuery = clone $queryBuilder;

            $subQuery->resetDQLPart('select');
            $subQuery->select(['o.id']);

            $orStatements = $subQuery->expr()->orX();

            $orStatements->add($subQuery->expr()->gt('sfr.closedAt', ':one_month_ago'));
            $subQuery->setParameter('one_month_ago', new \DateTime('1 month ago'));

            $orStatements->add($subQuery->expr()->in('sfr.status', ':open_statuses'));
            $subQuery->setParameter('open_statuses', self::OPEN_STATUSES);

            $subQuery->andWhere($orStatements);

            $ids = $subQuery->getQuery()->getScalarResult();

            $queryBuilder->andWhere($queryBuilder->expr()->notIn('o.id', array_column($ids, 'id')));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_OPEN__SALES_FORECAST_PROPERTY => [
                'property' => static::FILTER_OPEN__SALES_FORECAST_PROPERTY,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}
