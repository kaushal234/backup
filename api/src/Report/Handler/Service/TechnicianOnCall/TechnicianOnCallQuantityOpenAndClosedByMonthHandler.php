<?php

declare(strict_types=1);

namespace App\Report\Handler\Service\TechnicianOnCall;

use App\Entity\Service\TechnicianOnCall;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Options\TechnicianOnCallKpiOptions;
use App\Util\IriToId;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

class TechnicianOnCallQuantityOpenAndClosedByMonthHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    public function __construct(
        private readonly Connection $connection,
        private readonly IriToId $iriToId,
        private readonly TechnicianOnCallKpiOptions $technicianOnCallKpiOptions,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (TechnicianOnCall::class !== $resourceClass || 'month' !== $x || 'quantity' !== $y) {
            return null;
        }

        $options = $this->technicianOnCallKpiOptions->configureOptions($options);
        $types = [];

        $queryBuilderOpen = $this->connection->createQueryBuilder()
            ->select('COUNT(*) AS value')
            ->addSelect("'open' AS y")
            ->addSelect("DATE_FORMAT(t.created_at,'%Y-%m') AS x")
            ->from('technician_on_call', 't')
            ->where('t.created_at >= :from')
            ->andWhere('t.created_at < :to')
            ->groupBy('x')
            ->orderBy('x')
        ;

        $queryBuilderClosed = $this->connection->createQueryBuilder()
            ->select('COUNT(*) AS value')
            ->addSelect("'solved' AS y")
            ->addSelect("DATE_FORMAT(t.solved_at,'%Y-%m') AS x")
            ->from('technician_on_call', 't')
            ->where('t.solved_at >= :from')
            ->andWhere('t.solved_at < :to')
            ->groupBy('x')
            ->orderBy('x')
        ;

        $parameters = [
            'from' => $options['from']->format('Y-m-d H:i:s'),
            'to' => $options['to']->format('Y-m-d H:i:s'),
        ];

        if ($options['salesServiceOrganisation']) {
            $parameters['sso_id'] = $options['salesServiceOrganisation'];

            $queryBuilderOpen->andWhere('t.sales_organisation_service_id = :sso_id');
            $queryBuilderClosed->andWhere('t.sales_organisation_service_id = :sso_id');
        }

        if ($options['customers']) {
            $parameters['customers'] = $options['customers'];
            $types['customers'] = ArrayParameterType::INTEGER;

            $queryBuilderOpen->andWhere('t.customer_id in (:customers)');
            $queryBuilderClosed->andWhere('t.customer_id in (:customers)');
        }

        $sql = \sprintf('(%s) UNION ALL (%s) ORDER BY x ASC, y ASC',
            $queryBuilderOpen->getSQL(),
            $queryBuilderClosed->getSQL()
        );

        $results = $this->connection->executeQuery($sql, $parameters, $types)->fetchAllAssociative();

        return new ReportDataProvider(
            $results,
            (new LabelExtractor($results, 'x'))(),
            (new LabelExtractor($results, 'y'))()
        );
    }
}
