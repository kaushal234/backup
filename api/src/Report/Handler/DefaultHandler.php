<?php

declare(strict_types=1);

namespace App\Report\Handler;

use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;

class DefaultHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        return new ReportDataProvider(
            (new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))(),
            isset($options['includeAllColumns']) ? (new QueryBuilderExtractor($queriesBuilder->getXQueryBuilder()))() : null,
            isset($options['includeAllLines']) ? (new QueryBuilderExtractor($queriesBuilder->getYQueryBuilder()))() : null
        );
    }

    public static function getDefaultPriority(): int
    {
        return -1024;
    }
}
