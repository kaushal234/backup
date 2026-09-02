<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\Crab\KPI;

use App\Entity\Quality\Crab;
use App\Entity\Quality\CrabDepartment;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use Doctrine\ORM\Query\Expr\Join;

class CrabByDepartmentHandler extends AbstractCrabHandler
{
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (Crab::class !== $resourceClass || 'byDepartment' !== $x) {
            return null;
        }
        if (false === $this->buildDefaultRequest($x, $y, $options)) {
            return null;
        }

        $this->queryBuilder
            ->addSelect('cd.name as x')
            ->innerJoin(CrabDepartment::class, 'cd', Join::WITH, 'crab.department=cd')
            ->groupBy('x');

        return new ReportDataProvider(
            (new QueryBuilderExtractor($this->queryBuilder))(),
            [],
            []
        );
    }
}
