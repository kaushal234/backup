<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\SalesForecast;

use App\Entity\Sales\SalesForecast;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\Sales\AbstractSalesForecastBySSOFactoryHandler;

class SalesForecastCountBySSOFactoryRecentHandler extends AbstractSalesForecastBySSOFactoryHandler
{
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (SalesForecast::class !== $resourceClass || 'sso.name' !== $x || 'factory.name' !== $y || !isset($options['recently'])) {
            return null;
        }

        $mainQueryBuilder = $this->getBaseQuery($resourceClass, $x, $y, $options);

        $alias = $mainQueryBuilder->getRootAliases()[0];

        $mainQueryBuilder
            ->addSelect('x_0.id AS sso_id')
            ->addSelect('y_0.id AS factory_id')
        ;

        switch ($options['recently']) {
            case 'ordered':
                $status = SalesForecast::ORDERED;
                break;
            case 'lost':
                $status = SalesForecast::LOST;
                break;
            default:
                throw new \InvalidArgumentException('Argument for this report must be either lost or ordered');
        }

        $mainQueryBuilder->andWhere($mainQueryBuilder->expr()->eq(\sprintf('%s.status', $alias), ':status'));
        $mainQueryBuilder->andWhere(
            $mainQueryBuilder->expr()->gte(
                \sprintf('%s.closedAt', $alias),
                "'".(new \DateTime('2 month ago'))->format('Y-m-d')."'"
            )
        );

        $mainQueryBuilder->setParameter(':status', $status);

        $provider = new ReportDataProvider(
            (new QueryBuilderExtractor($mainQueryBuilder))(),
            null,
            null
        );

        return $this->generateMetadata($provider);
    }
}
