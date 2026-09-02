<?php

declare(strict_types=1);

namespace App\Report\Handler\Parts;

use App\Entity\Directory\Location;
use App\Entity\Parts\SparePartsRequest;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractSparePartsRequestHandler implements ReportHandlerInterface
{
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;

    protected function applyOptions(QueryBuilder $queryBuilder, array $options)
    {
        $queryBuilder
            ->andWhere('o.status != :status_merged')
            ->setParameter('status_merged', SparePartsRequest::STATUS_MERGED)
        ;

        if (null !== ($options['warranty'] ?? null)) {
            $warrantyOnly = \in_array($options['warranty'], [true, 'true', '1'], true);
            $queryBuilder
                ->andWhere(\sprintf('o.type %s= :type', $warrantyOnly ? '' : '!'))
                ->setParameter('type', SparePartsRequest::TYPE_WARRANTY)
            ;
        }
    }

    protected function generateMetadata(ReportDataProvider $provider): ReportDataProvider
    {
        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(Location::class, 'location_id')
            ->generate()
        );
    }
}
