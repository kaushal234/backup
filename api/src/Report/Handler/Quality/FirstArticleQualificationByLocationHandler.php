<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;

class FirstArticleQualificationByLocationHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function __construct(private readonly IriConverterInterface $iriConverter)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        // planApprovalStatus by location stays owned by FirstArticleQualificationPlanStatusByFactoryHandler,
        // which restricts the count to FAQ still IN_PROGRESS instead of all statuses.
        if (FirstArticleQualification::class !== $resourceClass
            || 'location.name' !== $x
            || 'status' !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $queriesBuilder->getMainQueryBuilder()
            ->addSelect('x_0.id AS location_id');

        $provider = new ReportDataProvider(
            (new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))()
        );

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(Location::class, 'location_id')
            ->generate()
        );
    }
}
