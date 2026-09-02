<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\Crab\KPI;

use App\Entity\Quality\Crab;
use App\Entity\Quality\CrabCode;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use Doctrine\ORM\Query\Expr\Join;

class CrabByCodeHandler extends AbstractCrabHandler
{
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (Crab::class !== $resourceClass || 'byCode' !== $x) {
            return null;
        }
        if (false === $this->buildDefaultRequest($x, $y, $options)) {
            return null;
        }

        $this->queryBuilder
            ->addselect('cc.description as x')
            ->innerJoin(CrabCode::class, 'cc', Join::WITH, 'crab.code = cc')
            ->groupBy('cc');

        return new ReportDataProvider(
            (new QueryBuilderExtractor($this->queryBuilder))(),
            [],
            []
        );
    }
}
