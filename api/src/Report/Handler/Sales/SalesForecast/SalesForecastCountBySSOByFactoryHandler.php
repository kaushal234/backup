<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\SalesForecast;

use App\Entity\Sales\SalesForecast;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\Sales\AbstractSalesForecastBySSOFactoryHandler;

class SalesForecastCountBySSOByFactoryHandler extends AbstractSalesForecastBySSOFactoryHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (SalesForecast::class !== $resourceClass || 'sso.name' !== $x || 'factory.name' !== $y || isset($options['recently'])) {
            return null;
        }
        $mainQueryBuilder = $this->getBaseQuery($resourceClass, $x, $y, $options);

        $alias = $mainQueryBuilder->getRootAliases()[0];

        $mainQueryBuilder
            ->addSelect('x_0.id AS sso_id')
            ->addSelect('y_0.id AS factory_id')
        ;

        if (isset($options['hot_deals']) && $options['hot_deals']) {
            $mainQueryBuilder->andWhere($mainQueryBuilder->expr()->gt(\sprintf('(%s.customerSuccessPercentage*%s.successPercentage)/100', $alias, $alias), ':percentage'));
            $mainQueryBuilder->andWhere(
                $mainQueryBuilder->expr()->between(
                    \sprintf('%s.estimatedSaleDate', $alias),
                    "'".(new \DateTime())->format('Y-m-d')."'",
                    "'".(new \DateTime('+ 2 months'))->format('Y-m-d')."'"
                )
            );
            $mainQueryBuilder->setParameter(':percentage', SalesForecast::HOT_DEALS_PERCENTAGE);
        }

        $orStatements = $mainQueryBuilder->expr()->orX();

        $orStatements->add($mainQueryBuilder->expr()->in(\sprintf('%s.status', $alias), SalesForecast::OPEN_STATUSES));

        $orStatements->add(
            $mainQueryBuilder->expr()->gt(
                \sprintf('%s.closedAt', $alias),
                "'".(new \DateTime('1 month ago'))->format('Y-m-d')."'"
            )
        );

        $mainQueryBuilder->andWhere($orStatements);

        $provider = new ReportDataProvider(
            (new QueryBuilderExtractor($mainQueryBuilder))(),
            null,
            null
        );

        return $this->generateMetadata($provider);
    }
}
