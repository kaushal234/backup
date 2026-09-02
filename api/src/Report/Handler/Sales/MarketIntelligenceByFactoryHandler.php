<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales;

use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\DBAL\Connection;

class MarketIntelligenceByFactoryHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    private readonly Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (MarketIntelligence::class !== $resourceClass || 'date' !== $x || 'locations' !== $y) {
            return null;
        }

        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->select('COUNT(m.id) as value')
            ->addSelect('bu.name as x')
            ->addSelect('"Value" as y')
            ->from('market_intelligence', 'm')
            ->leftJoin('m', 'user', 'p', 'p.id = m.poster_id')
            ->leftJoin('p', 'directory_businessunit', 'bu', 'bu.id = p.business_unit_id')
            ->where('m.created_at > :one_year_ago')
            ->groupBy('x')
            ->setParameter('one_year_ago', (new \DateTime('1 year ago'))->format('Y-m-d'))
        ;

        $results = $queryBuilder->executeQuery()->fetchAllAssociative();

        return new ReportDataProvider(
            $results,
            (new LabelExtractor($results, 'x'))(),
            (new LabelExtractor($results, 'y'))()
        );
    }
}
