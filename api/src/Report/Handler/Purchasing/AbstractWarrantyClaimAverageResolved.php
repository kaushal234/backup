<?php

declare(strict_types=1);

namespace App\Report\Handler\Purchasing;

use App\Report\Handler\IsGrantedTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractWarrantyClaimAverageResolved
{
    use IsGrantedTrait;

    private readonly Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('AVG(DATEDIFF(vwc.closed_at, vwc.created_at)) as value')
            ->addSelect("DATE_FORMAT(vwc.created_at,'%Y-%m') AS x")
            ->from('vendor_warranty_claims', 'vwc')
            ->leftJoin('vwc', 'vendor_warranty_claim_status', 'vwc_status', 'vwc_status.id = vwc.status_id')
            ->where("vwc_status.name LIKE 'CLOSED_RESOLVED%'")
            ->andWhere('vwc.created_at > :one_years_ago')
            ->groupBy('x', 'y')
            ->orderBy('x, y')
            ->setParameter('one_years_ago', (new \DateTime('midnight first day of this month last year'))->format('Y-m-d'), ParameterType::STRING)
        ;

        return $queryBuilder;
    }
}
