<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales;

use App\Entity\Sales\Demo;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\ORM\Query\Expr\Join;

class DemoNotClosedByLocationHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (Demo::class !== $resourceClass || 'status' !== $y || !\in_array($x, ['factory.name', 'sso.name'], true) || isset($options['template'])) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $capability = current(explode('.', $x));

        $queriesBuilder
            ->getXQueryBuilder()
            ->leftJoin(Demo::class, 'd', Join::ON, 'o.id = d.'.$capability)
            ->groupBy('o.id')
            ->having('COUNT(d.id) > 0')
            ->orderBy('o.name')
        ;

        return new ReportDataProvider(
            (new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))(),
            (new QueryBuilderExtractor($queriesBuilder->getXQueryBuilder()))(),
            (new QueryBuilderExtractor($queriesBuilder->getYQueryBuilder()))()
        );
    }
}
