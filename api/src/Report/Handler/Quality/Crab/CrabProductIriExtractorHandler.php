<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\Crab;

use App\Entity\Directory\Location;
use App\Entity\Quality\Crab;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\ORM\Query\Expr\Join;

class CrabProductIriExtractorHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (Crab::class !== $resourceClass || 'equipmentRecord.manufacturerLocation.name' !== $x || 'category' !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $alias = $queriesBuilder->getMainQueryBuilder()->getRootAliases()[0];

        $queriesBuilder->getMainQueryBuilder()
            ->addSelect('l.id AS factory_id')
            ->leftJoin(\sprintf('%s.equipmentRecord', $alias), 'er')
            ->leftJoin(Location::class, 'l', Join::WITH, 'er.manufacturerLocation = l.id')
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
