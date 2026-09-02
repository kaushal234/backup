<?php

declare(strict_types=1);

namespace App\Report\Handler;

use App\Entity\Quality\CalibratedTools\Tool;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;

class CalibratedToolStatusByFactoryHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (Tool::class !== $resourceClass || 'status' !== $x || 'locationArea.factory.name' !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $queriesBuilder->getYQueryBuilder()
            ->andWhere('o.capability.factory = :factory')
            ->orderBy('o.name')
            ->setParameter('factory', true);

        return new ReportDataProvider(
            (new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))(),
            (new QueryBuilderExtractor($queriesBuilder->getXQueryBuilder()))(),
            (new QueryBuilderExtractor($queriesBuilder->getYQueryBuilder()))()
        );
    }
}
