<?php

declare(strict_types=1);

namespace App\Filter\SalesForecast;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\SalesForecast;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SalesForecastHotDealsFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_HOT_DEALS_PROPERTY = 'hot_deals';
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

        if (!$request->query->has(static::FILTER_HOT_DEALS_PROPERTY)) {
            return;
        }

        $value = $request->query->get(static::FILTER_HOT_DEALS_PROPERTY);

        if (SalesForecast::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Sales Forecasts resource');
        }

        if (!\in_array($value, [true, 'true', '1'], true)) {
            return;
        }

        $orStatements = $queryBuilder->expr()->orX();

        $orStatements->add($queryBuilder->expr()->in('o.status', ':open_statuses'));
        $orStatements->add($queryBuilder->expr()->gt('o.closedAt', ':one_month_ago'));

        $queryBuilder->setParameter('open_statuses', SalesForecast::OPEN_STATUSES);
        $queryBuilder->setParameter('one_month_ago', new \DateTime('1 month ago'));

        $queryBuilder->andWhere($orStatements);

        $queryBuilder->andWhere($queryBuilder->expr()->gt('o.customerSuccessPercentage*o.successPercentage/100', ':percentage'));
        $queryBuilder->andWhere(
            $queryBuilder->expr()->between('o.estimatedSaleDate',
                "'".(new \DateTime())->format('Y-m-d')."'",
                "'".(new \DateTime('+ 2 months'))->format('Y-m-d')."'"
            )
        );

        $queryBuilder->setParameter(':percentage', SalesForecast::HOT_DEALS_PERCENTAGE);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_HOT_DEALS_PROPERTY => [
                'property' => static::FILTER_HOT_DEALS_PROPERTY,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}
