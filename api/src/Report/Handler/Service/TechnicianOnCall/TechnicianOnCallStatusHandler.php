<?php

declare(strict_types=1);

namespace App\Report\Handler\Service\TechnicianOnCall;

use App\Entity\Directory\Location;
use App\Entity\Service\TechnicianOnCall;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;

class TechnicianOnCallStatusHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (TechnicianOnCall::class !== $resourceClass || 'salesOrganisationService.name' !== $x || 'status' !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);
        $queryBuilder = $queriesBuilder->getMainQueryBuilder();
        $alias = $queryBuilder->getRootAliases()[0];

        $queriesBuilder->getMainQueryBuilder()
            ->addSelect('x_0.id AS sso_id')
            ->addSelect(
                \sprintf("(CASE %s.status
                WHEN 'PENDING' THEN 1
                WHEN 'IN_PROGRESS' THEN 2
                WHEN 'SUSPENDED' THEN 3
                WHEN 'SOLVED' THEN 4
                WHEN 'CLOSED' THEN 5
                ELSE 99
            END) AS HIDDEN status_priority", $alias)
            )
            ->orderBy('x_0.name', 'ASC')
            ->addOrderBy('status_priority', 'ASC')
        ;

        $provider = new ReportDataProvider(
            dataExtractor: (new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))(),
            doCleanUp: false
        );

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(Location::class, 'sso_id')
            ->generate()
        );
    }
}
