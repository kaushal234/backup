<?php

declare(strict_types=1);

namespace App\Report\Handler\Parts;

use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Parts\SparePartsRequest;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use App\Report\ReportQueriesBuilder;
use Doctrine\ORM\Query\Expr\Join;

class SparePartsRequestBySparePartsHubHandler extends AbstractSparePartsRequestHandler
{
    use DefaultPriorityTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (SparePartsRequest::class !== $resourceClass || 'sph.name' !== $x || 'status' !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $mainQueryBuilder = $queriesBuilder->getMainQueryBuilder();
        $queryNameGenerator = new QueryNameGenerator();
        $sphAlias = QueryBuilderHelper::addJoinOnce($mainQueryBuilder, $queryNameGenerator, 'o', 'sph', Join::LEFT_JOIN);
        $mainQueryBuilder->addSelect(\sprintf('%s.id AS location_id', $sphAlias));

        ReportQueriesBuilder::replaceSelectValuePart($mainQueryBuilder, 'COUNT(DISTINCT o) AS value');
        $this->applyOptions($mainQueryBuilder, $options);

        $dataExtractor = (new QueryBuilderExtractor($mainQueryBuilder))();
        $xLabelsExtractor = [];
        foreach ($dataExtractor as $item) {
            if (!\in_array($item['x'], array_column($xLabelsExtractor, 'x'), true)) {
                $xLabelsExtractor[] = ['x' => $item['x']];
            }
        }

        $provider = new ReportDataProvider(
            $dataExtractor,
            $xLabelsExtractor,
            array_map(static fn (string $status) => ['y' => $status], SparePartsRequest::STATUS_ALL),
        );

        return $this->generateMetadata($provider);
    }
}
