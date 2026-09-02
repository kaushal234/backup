<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\SalesForecast;

use App\Entity\Sales\SalesForecast;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use App\Report\ReportQueriesBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;

class SalesForecastCountByFactoryByFamilyForStandardLeadTimeHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (SalesForecast::class !== $resourceClass || 'product.family.name' !== $x || 'factory.name' !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);
        $mainQueryBuilder = $queriesBuilder->getMainQueryBuilder();

        if (\in_array($options['hot'] ?? false, [true, 'true', '1'], true)) {
            $mainQueryBuilder
                ->where('o.status IN (:statuses)')
                ->andWhere($mainQueryBuilder->expr()->gt('(o.customerSuccessPercentage*o.successPercentage)/100', ':percentage'))
                ->andWhere($mainQueryBuilder->expr()->between(
                    'o.estimatedSaleDate',
                    "'".(new \DateTime())->format('Y-m-d')."'",
                    "'".(new \DateTime('+ 2 months'))->format('Y-m-d')."'"
                ))
                ->setParameters(new ArrayCollection([
                    new Parameter('statuses', SalesForecast::OPEN_STATUSES),
                    new Parameter('percentage', SalesForecast::HOT_DEALS_PERCENTAGE),
                ]))
            ;

            if ($options['quantity'] ?? false) {
                ReportQueriesBuilder::replaceSelectValuePart($mainQueryBuilder, 'SUM(o.quantity) AS value');
            }
        }

        return new ReportDataProvider(
            (new QueryBuilderExtractor($mainQueryBuilder))(),
            null,
            null
        );
    }
}
