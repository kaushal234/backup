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

class TechnicianOnCallIndiceFactorHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (TechnicianOnCall::class !== $resourceClass || 'salesOrganisationService.name' !== $x || 'indiceFactor' !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);
        $queryBuilder = $queriesBuilder->getMainQueryBuilder();
        $alias = $queryBuilder->getRootAliases()[0];

        $queriesBuilder->getMainQueryBuilder()
            ->addSelect('x_0.id AS sso_id')
            ->andWhere($queryBuilder->expr()->in(\sprintf('%s.status', $alias), TechnicianOnCall::OPENED_STATUSES))
            ->orderBy($alias.'.indiceFactor', 'ASC')
            ->orderBy('x_0.name', 'ASC')
        ;

        $provider = new ReportDataProvider((new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))());

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(Location::class, 'sso_id')
            ->generate()
        );
    }
}
