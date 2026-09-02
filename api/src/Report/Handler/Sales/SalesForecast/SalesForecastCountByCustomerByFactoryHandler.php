<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\SalesForecast;

use App\Entity\Directory\Location;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerType;
use App\Entity\Sales\SalesForecast;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use App\Util\IriToId;

class SalesForecastCountByCustomerByFactoryHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    private readonly IriToId $iriToId;

    public function __construct(IriToId $iriToId)
    {
        $this->iriToId = $iriToId;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (SalesForecast::class !== $resourceClass || 'buyer.name' !== $x || 'factory.name' !== $y) {
            return null;
        }
        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $mainQueryBuilder = $queriesBuilder->getMainQueryBuilder();
        $alias = $mainQueryBuilder->getRootAliases()[0];

        $mainQueryBuilder
            ->leftJoin(\sprintf('%s.buyer', $alias), 'customer_buyer')
            ->leftJoin(\sprintf('%s.factory', $alias), 'factory')
        ;

        $mainQueryBuilder
            ->addSelect('customer_buyer.id AS buyer_id')
            ->addSelect('factory.id AS factory_id')
        ;

        $orStatements = $mainQueryBuilder->expr()->orX();

        if (isset($options['delinquent']) && $options['delinquent']) {
            $mainQueryBuilder->andWhere('o.delinquent = true');
        }

        if (isset($options['asm'])) {
            $orStatements->add($mainQueryBuilder->expr()->eq(\sprintf('%s.asm', $alias), $this->iriToId->getId($options['asm'])));
        }

        if (isset($options['military']) && $options['military']) {
            $mainQueryBuilder
                ->leftJoin(\sprintf('%s.endUser', $alias), 'customer_end_user')
                ->leftJoin('customer_end_user.customerTypes', 'type_end_user')
                ->leftJoin('customer_buyer.customerTypes', 'type_buyer')
            ;

            $orStatements->add($mainQueryBuilder->expr()->eq('type_end_user.name', ':military_type'));
            $orStatements->add($mainQueryBuilder->expr()->eq('type_buyer.name', ':military_type'));

            $mainQueryBuilder->setParameter('military_type', CustomerType::MILITARY_TYPE_NAME);
        }

        $mainQueryBuilder->andWhere($orStatements);

        $mainQueryBuilder->andWhere($mainQueryBuilder->expr()->in(\sprintf('%s.status', $alias), SalesForecast::OPEN_STATUSES));

        $provider = new ReportDataProvider(
            (new QueryBuilderExtractor($mainQueryBuilder))(),
            null,
            null
        );

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(Customer::class, 'buyer_id')
            ->setY(Location::class, 'factory_id')
            ->generate()
        );
    }
}
