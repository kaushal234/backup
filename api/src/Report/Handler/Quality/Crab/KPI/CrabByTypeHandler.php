<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\Crab\KPI;

use App\Entity\Quality\Crab;
use App\Entity\Sales\Product;
use App\Entity\Sales\ProductFamily;
use App\Entity\Sales\ProductType;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use Doctrine\ORM\Query\Expr\Join;

class CrabByTypeHandler extends AbstractCrabHandler
{
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (Crab::class !== $resourceClass || 'byType' !== $x) {
            return null;
        }
        if (false === $this->buildDefaultRequest($x, $y, $options)) {
            return null;
        }

        $this->queryBuilder
            ->addselect('t.englishName as x')
            ->leftJoin(Product::class, 'p', Join::ON, 'er.product = p')
            ->leftJoin(ProductFamily::class, 'f', Join::WITH, 'p.family = f')
            ->leftJoin(ProductType::class, 't', Join::WITH, 'f.productType = t')
            ->groupBy('x');

        return new ReportDataProvider(
            (new QueryBuilderExtractor($this->queryBuilder))(),
            [],
            []
        );
    }
}
