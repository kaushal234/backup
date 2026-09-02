<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\EquipmentShippingRecord;

use App\Entity\Directory\Location;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;

class EquipmentShippingRecordByManufacturerLocationByStatusHandler implements ReportHandlerInterface
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
        if (
            EquipmentShippingRecord::class !== $resourceClass
            || 'status' !== $y
            || 'equipmentShippingRecordLines.equipmentRecord.manufacturerLocation.name' !== $x
        ) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);
        $qb = $queriesBuilder->getMainQueryBuilder();

        $rootAlias = $qb->getRootAliases()[0];

        $qb->resetDQLPart('select');
        $qb->resetDQLPart('groupBy');

        $qb->leftJoin($rootAlias.'.equipmentShippingRecordLines', 'esrl');
        $qb->leftJoin('esrl.equipmentRecord', 'er');
        $qb->leftJoin('er.manufacturerLocation', 'dl');

        $qb
            ->addSelect('dl.id AS manufacturer_location_id')
            ->addSelect('dl.name AS x')
            ->addSelect(\sprintf('%s.status AS y', $rootAlias))
            ->addSelect(\sprintf('COUNT(DISTINCT %s.id) AS value', $rootAlias))
            ->andWhere('dl.id IS NOT NULL')
            ->andWhere('dl.capability.factory = 1')
        ;

        if (isset($options['status']) && \count($options['status']) > 0) {
            $qb
                ->andWhere(\sprintf('%s.status IN (:statuses)', $rootAlias))
                ->setParameter('statuses', $options['status']);
        }

        $qb
            ->groupBy('dl.id')
            ->addGroupBy('dl.name')
            ->addGroupBy(\sprintf('%s.status', $rootAlias))
        ;

        $provider = new ReportDataProvider(
            (new QueryBuilderExtractor($qb))()
        );

        return $provider->setMetadataExtractor(
            $this->irisExtractorBuilderFactory->createBuilder()
                ->setX(Location::class, 'manufacturer_location_id')
                ->generate()
        );
    }
}
