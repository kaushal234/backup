<?php

declare(strict_types=1);

namespace App\Report\Handler\Service\CustomerServiceRecord;

use App\Entity\Directory\Location;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;

class CustomerServiceRecordBySSOByStatusHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (AbstractCustomerServiceRecord::class !== $resourceClass || 'equipmentRecord.salesOrganisationService.name' !== $x || 'status' !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $alias = $queriesBuilder->getMainQueryBuilder()->getRootAliases()[0];

        $queriesBuilder->getMainQueryBuilder()
            ->addSelect('l.id AS factory_id')
            ->leftJoin(\sprintf('%s.equipmentRecord', $alias), 'er')
            ->leftJoin('er.salesOrganisationService', 'l')
            ->where('l.state.hidden = :false')
            ->setParameter('false', false)
        ;

        $provider = new ReportDataProvider((new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))());

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(Location::class, 'factory_id')
            ->generate()
        );
    }
}
