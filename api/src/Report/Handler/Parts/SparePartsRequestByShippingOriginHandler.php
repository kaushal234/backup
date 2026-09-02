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

class SparePartsRequestByShippingOriginHandler extends AbstractSparePartsRequestHandler
{
    use DefaultPriorityTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (SparePartsRequest::class !== $resourceClass || 'parts.shippingOrigin.name' !== $x || 'status' !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $mainQueryBuilder = $queriesBuilder->getMainQueryBuilder();
        $queryNameGenerator = new QueryNameGenerator();
        $partsAlias = QueryBuilderHelper::addJoinOnce($mainQueryBuilder, $queryNameGenerator, 'o', 'parts', Join::LEFT_JOIN);
        $shippingOriginAlias = QueryBuilderHelper::addJoinOnce($mainQueryBuilder, $queryNameGenerator, $partsAlias, 'shippingOrigin', Join::LEFT_JOIN);
        $mainQueryBuilder->addSelect(\sprintf('%s.id AS location_id', $shippingOriginAlias));

        ReportQueriesBuilder::replaceSelectValuePart($mainQueryBuilder, 'COUNT(DISTINCT o) AS value');

        $this->applyOptions($mainQueryBuilder, $options);

        $provider = new ReportDataProvider(
            (new QueryBuilderExtractor($mainQueryBuilder))(),
            null,
            array_map(static fn (string $status) => ['y' => $status], SparePartsRequest::STATUS_ALL),
        );

        return $this->generateMetadata($provider);
    }
}
