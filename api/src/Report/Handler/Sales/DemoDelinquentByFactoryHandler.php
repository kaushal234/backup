<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales;

use App\Entity\Sales\Demo;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\DBAL\Connection;

class DemoDelinquentByFactoryHandler implements ReportHandlerInterface
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
        if (Demo::class !== $resourceClass || 'delinquent' !== $y || !\in_array($x, ['factory.name', 'sso.name'], true)) {
            return null;
        }

        $queryBuilder = $this->connection->createQueryBuilder();

        $condition = 'factory.name' === $x ? 'factory_id' : 'sso_id';

        $queryBuilder
            ->addSelect('COUNT(d.id) as value')
            ->addSelect("'Delinquent' as y")
            ->addSelect('f.name AS x')
            ->from('demos', 'd')
            ->leftJoin('d', 'directory_location', 'f', 'd.'.$condition.' = f.id')
            ->where('d.delinquent = :delinquent')
            ->groupBy('y, x')
            ->orderBy('x, y')
            ->setParameter('delinquent', true)
        ;

        $results = $queryBuilder->executeQuery()->fetchAllAssociative();

        return new ReportDataProvider(
            $results,
            (new LabelExtractor($results, 'x'))(),
            (new LabelExtractor($results, 'y'))()
        );
    }
}
