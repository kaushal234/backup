<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality;

use App\Entity\Directory\Location;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;

class FirstArticleQualificationPlanStatusByFactoryHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (FirstArticleQualification::class !== $resourceClass || 'location.name' !== $x || 'planApprovalStatus' !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $queriesBuilder->getMainQueryBuilder()
            ->addSelect('x_0.id AS location_id')
            ->where('o.status IN (:statuses) ')
            ->setParameter('statuses', [
                FirstArticleQualification::IN_PROGRESS,
                FirstArticleQualification::IN_PROGRESS_PLAN_COMPLETED,
            ]);

        $queriesBuilder->getXQueryBuilder()
            ->andWhere('o.capability.factory = :factory')
            ->orderBy('o.name')
            ->setParameter('factory', true);

        $provider = new ReportDataProvider(
            (new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))(),
            null,
            (new QueryBuilderExtractor($queriesBuilder->getYQueryBuilder()))()
        );

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(Location::class, 'location_id')
            ->generate()
        );
    }
}
