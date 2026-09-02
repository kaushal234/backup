<?php

declare(strict_types=1);

namespace App\Report\Handler\Support;

use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;

class EquipmentRecordSalesOrganisationByFactoryHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (EquipmentRecord::class !== $resourceClass || 'salesOrganisation.name' !== $y || 'manufacturerLocation.name' !== $x) {
            return null;
        }
        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $queriesBuilder->getMainQueryBuilder()
            ->addSelect('x_0.id AS sales_organisation_id')
            ->addSelect('y_0.id AS manufacturer_location_id')
            ->where('o.dateShipped IS NULL')
        ;

        if (isset($options['preDeliveryInspections']) && true === (bool) $options['preDeliveryInspections']) {
            $dateInFutureDay = new \DateTime(\sprintf('Today %s days', $options['preDeliveryInspections']));

            $queriesBuilder->getMainQueryBuilder()
                ->leftJoin('o.orderFactory', 'orderFactory')
                ->leftJoin('orderFactory.orderLine', 'orderLine')
                ->andWhere('orderLine.inspection = :inspected')
                ->setParameter('inspected', true)
                ->andWhere('o.estimatedGreenTagDate BETWEEN NOW() AND :dateInFutureDays')
                ->setParameter('dateInFutureDays', $dateInFutureDay->format('Y-m-d'))
            ;
        }

        $provider = new ReportDataProvider((new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))());

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(Location::class, 'sales_organisation_id')
            ->setY(Location::class, 'manufacturer_location_id')
            ->generate()
        );
    }
}
