<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Sales\CustomerType;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use App\Util\IriToId;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractSalesForecastBySSOFactoryHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    protected IriConverterInterface $iriConverter;
    private readonly IriToId $iriToIdi;

    public function __construct(IriToId $iriToIdi, IriConverterInterface $iriConverter)
    {
        $this->iriToIdi = $iriToIdi;
        $this->iriConverter = $iriConverter;
    }

    protected function getBaseQuery(string $resourceClass, string $x, string $y, array $options = []): QueryBuilder
    {
        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $mainQueryBuilder = $queriesBuilder->getMainQueryBuilder();

        $alias = $mainQueryBuilder->getRootAliases()[0];

        if (isset($options['delinquent']) && $options['delinquent']) {
            $mainQueryBuilder->andWhere('o.delinquent = true');
        }

        if (isset($options['asm'])) {
            $mainQueryBuilder->andWhere($mainQueryBuilder->expr()->eq(\sprintf('%s.asm', $alias), $this->iriToIdi->getId($options['asm'])));
        }

        if (isset($options['military']) && $options['military']) {
            $mainQueryBuilder
                ->join(\sprintf('%s.endUser', $alias), 'customer_end_user')
                ->join(\sprintf('%s.buyer', $alias), 'customer_buyer')
                ->join('customer_end_user.customerTypes', 'type_end_user')
                ->join('customer_buyer.customerTypes', 'type_buyer')
            ;

            $orStatements = $mainQueryBuilder->expr()->orX();

            $orStatements->add($mainQueryBuilder->expr()->eq('type_end_user.name', ':military_type'));
            $orStatements->add($mainQueryBuilder->expr()->eq('type_buyer.name', ':military_type'));

            $mainQueryBuilder->andWhere($orStatements);

            $mainQueryBuilder->setParameter('military_type', CustomerType::MILITARY_TYPE_NAME);
        }

        $mainQueryBuilder
            ->andWhere('x_0.capability.sso = :sso_capability')
            ->andWhere('y_0.capability.factory = :factory_capability')
            ->setParameter('sso_capability', true)
            ->setParameter('factory_capability', true)
        ;

        return $mainQueryBuilder;
    }

    protected function generateMetadata(ReportDataProvider $provider): ReportDataProvider
    {
        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(Location::class, 'sso_id')
            ->setY(Location::class, 'factory_id')
            ->generate()
        );
    }
}
